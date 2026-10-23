<?php

namespace App\Filament\Pages;

use App\Exports\ReportArrayExport;
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

class StockReportByWarehousePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static string|UnitEnum|null $navigationGroup = 'Reports & Analytics';
    protected static ?string $navigationLabel = 'Stock Report by Warehouse';
    protected static ?string $title = 'Stock Report by Warehouse';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasMenuAccess(15) ?? false;
    }

    protected string $view = 'filament.pages.stock-report-by-warehouse-page';

    public ?array $data = [];
    public array $stock_report = [];
    public bool $hasSearched = false;
    public ?string $selected_warehouse_name = null;
    public ?string $selected_from_date = null;
    public ?string $selected_to_date = null;

    public function mount(): void
    {
        $this->form->fill([
            'filter_whid' => Warehouse::first()?->warehouseid ?? '',
            'filter_from' => now()->startOfMonth()->toDateString(),
            'filter_to' => now()->toDateString(),
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
                    ->options(Warehouse::selectOptions())
                    ->required()
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
        $this->stock_report = [];

        $warehouseId = $formData['filter_whid'] ?? null;
        $fromDate = $formData['filter_from'] ?? now()->startOfMonth()->toDateString();
        $toDate = $formData['filter_to'] ?? now()->toDateString();

        if (empty($warehouseId) || $fromDate > $toDate) {
            return;
        }

        $warehouse = Warehouse::where('warehouseid', $warehouseId)->first();
        $this->selected_warehouse_name = $warehouse ? "{$warehouse->warehouseid} - {$warehouse->warehouse_name}" : $warehouseId;
        $this->selected_from_date = date('d/m/Y', strtotime($fromDate));
        $this->selected_to_date = date('d/m/Y', strtotime($toDate));

        $transactions = "
            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty1, 'IN' AS type
            FROM transaction_headers m JOIN transaction_details d ON m.trans_id = d.trans_id
            WHERE m.status != 'X' AND m.trans_type = 'IN'
            UNION ALL
            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty1, 'OUT' AS type
            FROM transaction_headers m JOIN transaction_details d ON m.trans_id = d.trans_id
            WHERE m.status != 'X' AND m.trans_type = 'OUT'
            UNION ALL
            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty1, 'ADJ' AS type
            FROM transaction_headers m JOIN transaction_details d ON m.trans_id = d.trans_id
            WHERE m.status != 'X' AND m.trans_type = 'ADJ'
        ";

        $rows = DB::select("
            SELECT
                i.item_code,
                i.description,
                i.uom1,
                COALESCE(SUM(CASE WHEN t.trans_date < ? AND t.type = 'IN' THEN t.qty1 ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN t.trans_date < ? AND t.type = 'OUT' THEN t.qty1 ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN t.trans_date < ? AND t.type = 'ADJ' THEN t.qty1 ELSE 0 END), 0) AS opening_qty,
                COALESCE(SUM(CASE WHEN t.trans_date BETWEEN ? AND ? AND t.type = 'IN' THEN t.qty1 ELSE 0 END), 0) AS incoming_qty,
                COALESCE(SUM(CASE WHEN t.trans_date BETWEEN ? AND ? AND t.type = 'OUT' THEN t.qty1 ELSE 0 END), 0) AS outgoing_qty,
                COALESCE(SUM(CASE WHEN t.trans_date BETWEEN ? AND ? AND t.type = 'ADJ' THEN t.qty1 ELSE 0 END), 0) AS adjustment_qty
            FROM items i
            INNER JOIN ({$transactions}) t ON i.item_code = t.icode AND t.whid = ?
            GROUP BY i.item_code, i.description, i.uom1
            ORDER BY i.item_code ASC
        ", [
            $fromDate, $fromDate, $fromDate,
            $fromDate, $toDate,
            $fromDate, $toDate,
            $fromDate, $toDate,
            $warehouseId,
        ]);

        $this->stock_report = array_map(function ($row) {
            $opening = (float) $row->opening_qty;
            $incoming = (float) $row->incoming_qty;
            $outgoing = (float) $row->outgoing_qty;
            $adjustment = (float) $row->adjustment_qty;

            return [
                'item_code' => $row->item_code,
                'description' => $row->description,
                'uom1' => $row->uom1 ?? '',
                'opening_qty' => $opening,
                'incoming_qty' => $incoming,
                'outgoing_qty' => $outgoing,
                'adjustment_qty' => $adjustment,
                'ending_stock' => $opening + $incoming - $outgoing + $adjustment,
            ];
        }, $rows);
    }

    public function exportExcel()
    {
        $this->filterReport();
        $formData = $this->form->getState();
        $rows = [];

        foreach ($this->stock_report as $index => $item) {
            $rows[] = [
                $index + 1, $item['item_code'], $item['description'], $item['uom1'],
                $item['opening_qty'], $item['incoming_qty'], $item['outgoing_qty'], $item['adjustment_qty'], $item['ending_stock'],
            ];
        }

        return Excel::download(
            new ReportArrayExport(['No', 'Item Code', 'Description', 'UOM', 'Opening Balance Qty', 'Incoming Qty', 'Outgoing Qty', 'Adjustment Qty', 'Ending Stock Qty'], $rows, 'Stock by Warehouse'),
            "stock-report-by-warehouse_{$formData['filter_from']}_{$formData['filter_to']}.xlsx",
        );
    }
}