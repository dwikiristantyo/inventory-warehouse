<?php

namespace App\Filament\Pages;

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
use UnitEnum;

class ReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan & Analytics';
    protected static ?string $navigationLabel = 'Laporan Transaksi';
    protected static ?string $title = 'Laporan Mutasi Stok Detail';

    protected string $view = 'filament.pages.report-page';

    public ?array $data = [];
    public array $items_report = [];

    public function mount(): void
    {
        $defaultWarehouse = Warehouse::first()?->warehouseid ?? '';

        $this->form->fill([
            'filter_whid'   => $defaultWarehouse,
            'filter_from'   => now()->startOfMonth()->toDateString(),
            'filter_to'     => now()->toDateString(),
            'filter_icodes' => ['ALL'],
        ]);

        $this->filterReport();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('filter_whid')
                    ->label('Gudang / Farm')
                    ->options(Warehouse::pluck('warehouse_name', 'warehouseid'))
                    ->required()
                    ->native(false),

                Select::make('filter_icodes')
                    ->label('Pilih Barang')
                    ->options(array_merge(
                        ['ALL' => '-- SEMUA BARANG --'],
                        Item::pluck('description', 'item_code')->toArray()
                    ))
                    ->multiple()
                    ->default(['ALL'])
                    ->native(false),

                DatePicker::make('filter_from')
                    ->label('Dari Tanggal')
                    ->required(),

                DatePicker::make('filter_to')
                    ->label('Sampai Tanggal')
                    ->required(),
            ])
            ->columns(4)
            ->statePath('data');
    }

    public function filterReport(): void
    {
        $formData     = $this->form->getState();
        $filterWhid   = $formData['filter_whid'] ?? null;
        $filterFrom   = $formData['filter_from'] ?? now()->startOfMonth()->toDateString();
        $filterTo     = $formData['filter_to'] ?? now()->toDateString();
        $filterIcodes = $formData['filter_icodes'] ?? ['ALL'];

        if (is_string($filterIcodes)) {
            $filterIcodes = [$filterIcodes];
        }

        if (empty($filterWhid)) {
            $this->items_report = [];
            return;
        }

        // Fetch master items
        $queryItems = Item::query();
        if (! in_array('ALL', $filterIcodes) && ! empty($filterIcodes)) {
            $queryItems->whereIn('item_code', $filterIcodes);
        }
        $masterItems = $queryItems->orderBy('item_code', 'asc')->get();

        $this->items_report = [];

        foreach ($masterItems as $item) {
            $icode = $item->item_code;

            // Disesuaikan: JOIN menggunakan m.trans_id = d.trans_id
            $unionQuery = "
                SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty, d.qty_uom2 AS qty2, 'IN' AS type, m.trans_id, m.remark
                FROM transaction_headers m 
                JOIN transaction_details d ON m.trans_id = d.trans_id 
                WHERE m.status != 'X' AND m.trans_type = 'IN'

                UNION ALL

                SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty, d.qty_uom2 AS qty2, 'OUT' AS type, m.trans_id, m.remark
                FROM transaction_headers m 
                JOIN transaction_details d ON m.trans_id = d.trans_id 
                WHERE m.status != 'X' AND m.trans_type = 'OUT'

                UNION ALL

                SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty, d.qty_uom2 AS qty2, 'ADJ' AS type, m.trans_id, m.remark
                FROM transaction_headers m 
                JOIN transaction_details d ON m.trans_id = d.trans_id 
                WHERE m.status != 'X' AND m.trans_type = 'ADJ'
            ";

            // A. Saldo Awal (Sebelum tanggal filter_from)
            $initBal = DB::selectOne("
                SELECT 
                    COALESCE(SUM(CASE WHEN t.type = 'IN' THEN t.qty ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN t.type = 'OUT' THEN t.qty ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN t.type = 'ADJ' THEN t.qty ELSE 0 END), 0) AS init_kg,
                    
                    COALESCE(SUM(CASE WHEN t.type = 'IN' THEN t.qty2 ELSE 0 END), 0) -
                    COALESCE(SUM(CASE WHEN t.type = 'OUT' THEN t.qty2 ELSE 0 END), 0) +
                    COALESCE(SUM(CASE WHEN t.type = 'ADJ' THEN t.qty2 ELSE 0 END), 0) AS init_pcs
                FROM ({$unionQuery}) t
                WHERE t.whid = ? AND t.icode = ? AND t.trans_date < ?
            ", [$filterWhid, $icode, $filterFrom]);

            $runningKg  = (float) ($initBal->init_kg ?? 0);
            $runningPcs = (float) ($initBal->init_pcs ?? 0);

            // B. Query Mutasi Transaksi
            $mutations = DB::select("
                SELECT 
                    t.trans_date, t.trans_id, t.remark, t.type,
                    t.qty AS kg, t.qty2 AS pcs
                FROM ({$unionQuery}) t
                WHERE t.whid = ? AND t.icode = ? AND t.trans_date BETWEEN ? AND ?
                ORDER BY t.trans_date ASC, CASE t.type WHEN 'ADJ' THEN 1 WHEN 'IN' THEN 2 WHEN 'OUT' THEN 3 END ASC, t.trans_id ASC
            ", [$filterWhid, $icode, $filterFrom, $filterTo]);

            $groupedMutations = [];
            $firstDate = date('Y-m-d', strtotime($filterFrom . ' -1 day'));

            // Baris Pertama: Closing Balance
            $groupedMutations[$firstDate][] = [
                'type'    => 'INIT',
                'remark'  => 'Closing Balance',
                'in_kg'   => 0, 'in_pcs'  => 0,
                'out_kg'  => 0, 'out_pcs' => 0,
                'adj_kg'  => 0, 'adj_pcs' => 0,
                'bal_kg'  => $runningKg,
                'bal_pcs' => $runningPcs,
            ];

            foreach ($mutations as $m) {
                $date   = $m->trans_date;
                $inKg   = ($m->type === 'IN')  ? (float) $m->kg  : 0;
                $inPcs  = ($m->type === 'IN')  ? (float) $m->pcs : 0;
                $outKg  = ($m->type === 'OUT') ? (float) $m->kg  : 0;
                $outPcs = ($m->type === 'OUT') ? (float) $m->pcs : 0;
                $adjKg  = ($m->type === 'ADJ') ? (float) $m->kg  : 0;
                $adjPcs = ($m->type === 'ADJ') ? (float) $m->pcs : 0;

                $runningKg  += ($inKg - $outKg + $adjKg);
                $runningPcs += ($inPcs - $outPcs + $adjPcs);

                $groupedMutations[$date][] = [
                    'type'    => $m->type,
                    'remark'  => $m->remark ?: ($m->type === 'IN' ? 'Barang Masuk' : ($m->type === 'OUT' ? 'Barang Keluar' : 'Adjustment')),
                    'in_kg'   => $inKg,   'in_pcs'  => $inPcs,
                    'out_kg'  => $outKg,  'out_pcs' => $outPcs,
                    'adj_kg'  => $adjKg,  'adj_pcs' => $adjPcs,
                    'bal_kg'  => $runningKg,
                    'bal_pcs' => $runningPcs,
                ];
            }

            $this->items_report[] = [
                'icode'     => $item->item_code,
                'desc1'     => $item->description,
                'uom1'      => $item->uom1, // Ditambahkan agar terbaca di Blade
                'uom2'      => $item->uom2, // Ditambahkan agar terbaca di Blade
                'grouped'   => $groupedMutations,
                'final_kg'  => $runningKg,
                'final_pcs' => $runningPcs,
            ];
        }
    }
}