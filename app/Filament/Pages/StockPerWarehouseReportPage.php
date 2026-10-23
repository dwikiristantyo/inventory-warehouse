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
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class StockPerWarehouseReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';
    protected static string|UnitEnum|null $navigationGroup = 'Reports & Analytics';
    protected static ?string $navigationLabel = 'Egg Stock Report By Warehouse';
    protected static ?string $title = 'Egg Stock Report By Warehouse';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasMenuAccess(2) ?? false;
    }

    protected string $view = 'filament.pages.stock-per-warehouse-report-page';

    public ?array $data = [];
    public array $stock_report = [];
    public ?string $selected_warehouse_name = '';
    public ?string $selected_to_date = '';
    public bool $hasSearched = false;

    public function mount(): void
    {
        $defaultWarehouse = Warehouse::first()?->warehouseid ?? '';

        $this->form->fill([
            'filter_whid' => $defaultWarehouse,
            'filter_from' => now()->startOfMonth()->toDateString(),
            'filter_to'   => now()->toDateString(),
        ]);

    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                DatePicker::make('filter_from')
                    ->label('From Date')
                    ->required(),

                DatePicker::make('filter_to')
                    ->label('To Date')
                    ->required(),

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
        $formData   = $this->form->getState();
        $this->hasSearched = true;
        $filterWhid = $formData['filter_whid'] ?? null;
        $filterFrom = $formData['filter_from'] ?? now()->startOfMonth()->toDateString();
        $filterTo   = $formData['filter_to'] ?? now()->toDateString();

        if (empty($filterWhid)) {
            $this->stock_report = [];
            return;
        }

        $warehouse = Warehouse::where('warehouseid', $filterWhid)->first();
        $this->selected_warehouse_name = $warehouse ? "{$warehouse->warehouseid} - {$warehouse->warehouse_name}" : $filterWhid;
        $this->selected_to_date = date('d-M-Y', strtotime($filterTo));

        // Subquery gabungan seluruh transaksi (Qty1 & Qty2)
        $unionQuery = "
            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty1, d.qty_uom2 AS qty2, 'IN' AS type
            FROM transaction_headers m 
            JOIN transaction_details d ON m.trans_id = d.trans_id 
            WHERE m.status != 'X' AND m.trans_type = 'IN'

            UNION ALL

            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty1, d.qty_uom2 AS qty2, 'OUT' AS type
            FROM transaction_headers m 
            JOIN transaction_details d ON m.trans_id = d.trans_id 
            WHERE m.status != 'X' AND m.trans_type = 'OUT'

            UNION ALL

            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty1, d.qty_uom2 AS qty2, 'ADJ' AS type
            FROM transaction_headers m 
            JOIN transaction_details d ON m.trans_id = d.trans_id 
            WHERE m.status != 'X' AND m.trans_type = 'ADJ'
        ";

        // Aggregasi data per barang
        $reportData = DB::select("
            SELECT 
                i.item_code,
                i.description,
                i.uom1,
                i.uom2,
                
                -- Saldo Awal (Sebelum From Date)
                COALESCE(SUM(CASE WHEN t.trans_date < ? AND t.type = 'IN' THEN t.qty1 ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN t.trans_date < ? AND t.type = 'OUT' THEN t.qty1 ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN t.trans_date < ? AND t.type = 'ADJ' THEN t.qty1 ELSE 0 END), 0) AS bal_qty1,

                COALESCE(SUM(CASE WHEN t.trans_date < ? AND t.type = 'IN' THEN t.qty2 ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN t.trans_date < ? AND t.type = 'OUT' THEN t.qty2 ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN t.trans_date < ? AND t.type = 'ADJ' THEN t.qty2 ELSE 0 END), 0) AS bal_qty2,

                -- Incoming
                COALESCE(SUM(CASE WHEN t.trans_date BETWEEN ? AND ? AND t.type = 'IN' THEN t.qty1 ELSE 0 END), 0) AS in_qty1,
                COALESCE(SUM(CASE WHEN t.trans_date BETWEEN ? AND ? AND t.type = 'IN' THEN t.qty2 ELSE 0 END), 0) AS in_qty2,

                -- Outgoing
                COALESCE(SUM(CASE WHEN t.trans_date BETWEEN ? AND ? AND t.type = 'OUT' THEN t.qty1 ELSE 0 END), 0) AS out_qty1,
                COALESCE(SUM(CASE WHEN t.trans_date BETWEEN ? AND ? AND t.type = 'OUT' THEN t.qty2 ELSE 0 END), 0) AS out_qty2,

                -- Adjustment
                COALESCE(SUM(CASE WHEN t.trans_date BETWEEN ? AND ? AND t.type = 'ADJ' THEN t.qty1 ELSE 0 END), 0) AS adj_qty1,
                COALESCE(SUM(CASE WHEN t.trans_date BETWEEN ? AND ? AND t.type = 'ADJ' THEN t.qty2 ELSE 0 END), 0) AS adj_qty2

            FROM items i
            LEFT JOIN ({$unionQuery}) t ON i.item_code = t.icode AND t.whid = ?
            GROUP BY i.item_code, i.description, i.uom1, i.uom2
            ORDER BY i.item_code ASC
        ", [
            $filterFrom, $filterFrom, $filterFrom,
            $filterFrom, $filterFrom, $filterFrom,
            $filterFrom, $filterTo,
            $filterFrom, $filterTo,
            $filterFrom, $filterTo,
            $filterFrom, $filterTo,
            $filterFrom, $filterTo,
            $filterFrom, $filterTo,
            $filterWhid
        ]);

        $this->stock_report = array_map(function ($row) {
            $bal1 = (float) $row->bal_qty1;
            $bal2 = (float) $row->bal_qty2;
            $in1  = (float) $row->in_qty1;
            $in2  = (float) $row->in_qty2;
            $out1 = (float) $row->out_qty1;
            $out2 = (float) $row->out_qty2;
            $adj1 = (float) $row->adj_qty1;
            $adj2 = (float) $row->adj_qty2;

            $stock1 = $bal1 + $in1 - $out1 + $adj1;
            $stock2 = $bal2 + $in2 - $out2 + $adj2;

            return [
                'item_code'   => $row->item_code,
                'description' => $row->description,
                'uom1'        => $row->uom1 ?? '',
                'uom2'        => $row->uom2 ?? '',
                'bal_qty1'    => $bal1,
                'bal_qty2'    => $bal2,
                'in_qty1'     => $in1,
                'in_qty2'     => $in2,
                'out_qty1'    => $out1,
                'out_qty2'    => $out2,
                'stock_qty1'  => $stock1,
                'stock_qty2'  => $stock2,
            ];
        }, $reportData);
    }

    public function exportExcel()
    {
        $this->filterReport();
        $formData = $this->form->getState();
        $rows = [];

        foreach ($this->stock_report as $index => $item) {
            $rows[] = [
                $index + 1,
                $item['item_code'],
                $item['bal_qty1'], $item['uom1'], $item['bal_qty2'], $item['uom2'],
                $item['in_qty1'], $item['uom1'], $item['in_qty2'], $item['uom2'],
                $item['out_qty1'], $item['uom1'], $item['out_qty2'], $item['uom2'],
                $item['stock_qty1'], $item['uom1'], $item['stock_qty2'], $item['uom2'],
            ];
        }

        return Excel::download(
            new ReportArrayExport([
                'No', 'Item Code',
                'Balance Qty1', 'Balance UOM1', 'Balance Qty2', 'Balance UOM2',
                'Incoming Qty1', 'Incoming UOM1', 'Incoming Qty2', 'Incoming UOM2',
                'Outgoing Qty1', 'Outgoing UOM1', 'Outgoing Qty2', 'Outgoing UOM2',
                'Stock Qty1', 'Stock UOM1', 'Stock Qty2', 'Stock UOM2',
            ], $rows, 'Stock per Warehouse'),
            "stock-per-warehouse_{$formData['filter_from']}_{$formData['filter_to']}.xlsx",
        );
    }
}