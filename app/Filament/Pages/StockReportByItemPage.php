<?php

namespace App\Filament\Pages;

use App\Exports\ReportArrayExport;
use App\Models\Item;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use UnitEnum;

class StockReportByItemPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string|UnitEnum|null $navigationGroup = 'Reports & Analytics';
    protected static ?string $navigationLabel = 'Stock Report by Item';
    protected static ?string $title = 'Stock Report by Item';

    protected string $view = 'filament.pages.stock-report-by-item-page';

    public ?array $data = [];
    public array $items_report = [];
    public bool $hasSearched = false;

    public function mount(): void
    {
        $this->form->fill([
            'filter_whid' => Warehouse::first()?->warehouseid ?? '',
            'filter_from' => now()->startOfMonth()->toDateString(),
            'filter_to' => now()->toDateString(),
            'filter_icodes' => ['ALL'],
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                DatePicker::make('filter_from')->label('From Date')->required(),
                DatePicker::make('filter_to')->label('To Date')->required(),
                Select::make('filter_whid')
                    ->label('Select Warehouse')
                    ->options(Warehouse::pluck('warehouse_name', 'warehouseid'))
                    ->required()
                    ->native(false)
                    ->columnSpanFull(),
                Select::make('filter_icodes')
                    ->label('Select Item')
                    ->options(array_merge(['ALL' => '-- ALL ITEMS --'], Item::orderBy('item_code')->pluck('description', 'item_code')->toArray()))
                    ->multiple()
                    ->default(['ALL'])
                    ->native(false)
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function filterReport(): void
    {
        $formData = $this->form->getState();
        $this->hasSearched = true;
        $this->items_report = [];

        $warehouseId = $formData['filter_whid'] ?? null;
        $fromDate = $formData['filter_from'] ?? now()->startOfMonth()->toDateString();
        $toDate = $formData['filter_to'] ?? now()->toDateString();
        $itemCodes = $formData['filter_icodes'] ?? ['ALL'];
        $itemCodes = is_array($itemCodes) ? $itemCodes : [$itemCodes];

        if (empty($warehouseId) || $fromDate > $toDate) {
            return;
        }

        $itemsQuery = Item::query()->orderBy('item_code');
        if (! in_array('ALL', $itemCodes, true) && $itemCodes !== []) {
            $itemsQuery->whereIn('item_code', $itemCodes);
        }

        $transactions = "
            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty, 'IN' AS type, m.trans_id, m.remark
            FROM transaction_headers m JOIN transaction_details d ON m.trans_id = d.trans_id
            WHERE m.status != 'X' AND m.trans_type = 'IN'
            UNION ALL
            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty, 'OUT' AS type, m.trans_id, m.remark
            FROM transaction_headers m JOIN transaction_details d ON m.trans_id = d.trans_id
            WHERE m.status != 'X' AND m.trans_type = 'OUT'
            UNION ALL
            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty, 'ADJ' AS type, m.trans_id, m.remark
            FROM transaction_headers m JOIN transaction_details d ON m.trans_id = d.trans_id
            WHERE m.status != 'X' AND m.trans_type = 'ADJ'
        ";

        foreach ($itemsQuery->get() as $item) {
            $opening = DB::selectOne("
                SELECT
                    COALESCE(SUM(CASE WHEN type = 'IN' THEN qty ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN type = 'OUT' THEN qty ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN type = 'ADJ' THEN qty ELSE 0 END), 0) AS opening_qty
                FROM ({$transactions}) t
                WHERE whid = ? AND icode = ? AND trans_date < ?
            ", [$warehouseId, $item->item_code, $fromDate]);

            $runningQty = (float) ($opening->opening_qty ?? 0);
            $mutations = DB::select("
                SELECT trans_date, trans_id, remark, type, qty
                FROM ({$transactions}) t
                WHERE whid = ? AND icode = ? AND trans_date BETWEEN ? AND ?
                ORDER BY trans_date ASC, CASE type WHEN 'ADJ' THEN 1 WHEN 'IN' THEN 2 WHEN 'OUT' THEN 3 END, trans_id ASC
            ", [$warehouseId, $item->item_code, $fromDate, $toDate]);

            $firstDate = date('Y-m-d', strtotime($fromDate . ' -1 day'));
            $grouped = [
                $firstDate => [[
                    'type' => 'INIT',
                    'remark' => 'Opening Balance',
                    'opening_qty' => $runningQty,
                    'incoming_qty' => 0,
                    'outgoing_qty' => 0,
                    'adjustment_qty' => 0,
                    'ending_stock' => $runningQty,
                ]],
            ];

            foreach ($mutations as $mutation) {
                $qty = (float) $mutation->qty;
                $incoming = $mutation->type === 'IN' ? $qty : 0;
                $outgoing = $mutation->type === 'OUT' ? $qty : 0;
                $adjustment = $mutation->type === 'ADJ' ? $qty : 0;
                $openingQty = $runningQty;
                $runningQty += $incoming - $outgoing + $adjustment;

                $grouped[$mutation->trans_date][] = [
                    'type' => $mutation->type,
                    'remark' => $mutation->remark ?: match ($mutation->type) {
                        'IN' => 'Goods In',
                        'OUT' => 'Goods Out',
                        default => 'Adjustment',
                    },
                    'opening_qty' => $openingQty,
                    'incoming_qty' => $incoming,
                    'outgoing_qty' => $outgoing,
                    'adjustment_qty' => $adjustment,
                    'ending_stock' => $runningQty,
                ];
            }

            $this->items_report[] = [
                'item_code' => $item->item_code,
                'description' => $item->description,
                'uom1' => $item->uom1,
                'grouped' => $grouped,
                'ending_stock' => $runningQty,
            ];
        }
    }

    public function exportExcel()
    {
        $this->filterReport();
        $formData = $this->form->getState();
        $rows = [];

        foreach ($this->items_report as $item) {
            foreach ($item['grouped'] as $date => $entries) {
                foreach ($entries as $entry) {
                    $rows[] = [
                        $item['item_code'], $item['description'], $item['uom1'], date('d-m-Y', strtotime($date)), $entry['remark'],
                        $entry['opening_qty'], $entry['incoming_qty'], $entry['outgoing_qty'], $entry['adjustment_qty'], $entry['ending_stock'],
                    ];
                }
            }
            $rows[] = [$item['item_code'], $item['description'], $item['uom1'], '', 'ITEM ENDING STOCK', '', '', '', '', $item['ending_stock']];
        }

        return Excel::download(
            new ReportArrayExport(['Item Code', 'Description', 'UOM 1', 'Date', 'Remark', 'Opening Qty 1', 'Incoming Qty 1', 'Outgoing Qty 1', 'Adjustment Qty 1', 'Ending Stock Qty 1'], $rows, 'Stock by Item'),
            "stock-report-by-item_{$formData['filter_from']}_{$formData['filter_to']}.xlsx",
        );
    }
}