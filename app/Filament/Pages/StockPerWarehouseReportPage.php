<?php

namespace App\Filament\Pages;

use App\Models\Warehouse;
use BackedEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class StockPerWarehouseReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-archive-box';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan & Analytics';
    protected static ?string $navigationLabel = 'Report Stock per Gudang';
    protected static ?string $title = 'REPORT STOCK PER GUDANG';

    protected string $view = 'filament.pages.stock-per-warehouse-report-page';

    public ?array $data = [];
    public array $stock_report = [];
    public ?string $selected_warehouse_name = '';
    public ?string $selected_to_date = '';

    public function mount(): void
    {
        $defaultWarehouse = Warehouse::first()?->warehouseid ?? '';

        $this->form->fill([
            'filter_whid' => $defaultWarehouse,
            'filter_from' => now()->startOfMonth()->toDateString(),
            'filter_to'   => now()->toDateString(),
        ]);

        $this->filterReport();
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
                    ->options(Warehouse::pluck('warehouse_name', 'warehouseid'))
                    ->required()
                    ->native(false),
            ])
            ->columns(3)
            ->statePath('data');
    }

    public function filterReport(): void
    {
        $formData   = $this->form->getState();
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
}