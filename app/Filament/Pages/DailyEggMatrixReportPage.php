<?php

namespace App\Filament\Pages;

use App\Exports\DailyEggMatrixExport;
use App\Models\Warehouse;
use BackedEnum;
use DateInterval;
use DatePeriod;
use DateTime;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class DailyEggMatrixReportPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-table-cells';
    protected static string|UnitEnum|null $navigationGroup = 'Reports & Analytics';
    protected static ?string $navigationLabel = 'Daily Egg Matrix Report';
    protected static ?string $title = 'Daily Egg Matrix Report';

    protected string $view = 'filament.pages.daily-egg-matrix-report-page';

    public ?array $data = [];
    public array $daily_data = [];
    public bool $hasSearched = false;

    protected array $salable_icodes = ['EG0001', 'EGG-SALABLE', 'EGG-GRADE-A', 'EGG-GRADE-B'];
    protected array $cull_icodes    = ['EG0002', 'EGG-CULL', 'EGG-AFKIR', 'EGG-CRACK'];

    public function mount(): void
    {
        $defaultWarehouse = Warehouse::first();

        $this->form->fill([
            'filter_whid' => $defaultWarehouse?->warehouseid ?? '',
            'filter_from' => now()->startOfMonth()->toDateString(),
            'filter_to'   => now()->toDateString(),
        ]);

    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                DatePicker::make('filter_from')
                    ->label('Dari Tanggal')
                    ->required(),

                DatePicker::make('filter_to')
                    ->label('Sampai Tanggal')
                    ->required(),

                Select::make('filter_whid')
                    ->label('Gudang / Farm')
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

        $this->daily_data = [];

        if (empty($filterWhid)) {
            return;
        }

        // 1. Query Saldo Awal Kumulatif (sebelum filter_from)
        $unionSubquery = "
            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty, d.qty_uom2 AS qty2, 'IN' AS type 
            FROM transaction_headers m JOIN transaction_details d ON m.trans_id = d.trans_id WHERE m.status != 'X' AND m.trans_type = 'IN'

            UNION ALL

            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty, d.qty_uom2 AS qty2, 'OUT' AS type 
            FROM transaction_headers m JOIN transaction_details d ON m.trans_id = d.trans_id WHERE m.status != 'X' AND m.trans_type = 'OUT'

            UNION ALL

            SELECT m.trans_date, m.warehouseid AS whid, d.item_code AS icode, d.qty_uom1 AS qty, d.qty_uom2 AS qty2, 'ADJ' AS type 
            FROM transaction_headers m JOIN transaction_details d ON m.trans_id = d.trans_id WHERE m.status != 'X' AND m.trans_type = 'ADJ'
        ";

        $initBal = DB::selectOne("
            SELECT 
                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->salable_icodes) . "') AND type = 'IN' THEN qty ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->salable_icodes) . "') AND type = 'OUT' THEN qty ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->salable_icodes) . "') AND type = 'ADJ' THEN qty ELSE 0 END), 0) AS init_salable_kg,

                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->salable_icodes) . "') AND type = 'IN' THEN qty2 ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->salable_icodes) . "') AND type = 'OUT' THEN qty2 ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->salable_icodes) . "') AND type = 'ADJ' THEN qty2 ELSE 0 END), 0) AS init_salable_pcs,

                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->cull_icodes) . "') AND type = 'IN' THEN qty ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->cull_icodes) . "') AND type = 'OUT' THEN qty ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->cull_icodes) . "') AND type = 'ADJ' THEN qty ELSE 0 END), 0) AS init_cull_kg,

                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->cull_icodes) . "') AND type = 'IN' THEN qty2 ELSE 0 END), 0) -
                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->cull_icodes) . "') AND type = 'OUT' THEN qty2 ELSE 0 END), 0) +
                COALESCE(SUM(CASE WHEN icode IN ('" . implode("','", $this->cull_icodes) . "') AND type = 'ADJ' THEN qty2 ELSE 0 END), 0) AS init_cull_pcs
            FROM ({$unionSubquery}) t
            WHERE t.whid = ? AND t.trans_date < ?
        ", [$filterWhid, $filterFrom]);

        $running_salable_kg  = (float) ($initBal->init_salable_kg ?? 0);
        $running_salable_pcs = (float) ($initBal->init_salable_pcs ?? 0);
        $running_cull_kg     = (float) ($initBal->init_cull_kg ?? 0);
        $running_cull_pcs    = (float) ($initBal->init_cull_pcs ?? 0);

        $period = new DatePeriod(
            new DateTime($filterFrom),
            new DateInterval('P1D'),
            (new DateTime($filterTo))->modify('+1 day')
        );

        $daysIndo = ['Sun' => 'Minggu', 'Mon' => 'Senin', 'Tue' => 'Selasa', 'Wed' => 'Rabu', 'Thu' => 'Kamis', 'Fri' => 'Jumat', 'Sat' => 'Sabtu'];

        foreach ($period as $dt) {
            $dateStr       = $dt->format('Y-m-d');
            $dayName       = $daysIndo[$dt->format('D')];
            $formattedDate = $dt->format('d-M');

            $dayTrans = DB::select("
                SELECT 
                    d.item_code AS icode, COALESCE(t.remark, '') AS remark, d.qty_uom1 AS qty, d.qty_uom2 AS qty2, t.type
                FROM (
                    SELECT trans_id AS id, trans_date, warehouseid AS whid, remark, 'IN' AS type FROM transaction_headers WHERE status != 'X' AND trans_type = 'IN'
                    UNION ALL
                    SELECT trans_id AS id, trans_date, warehouseid AS whid, remark, 'OUT' AS type FROM transaction_headers WHERE status != 'X' AND trans_type = 'OUT'
                    UNION ALL
                    SELECT trans_id AS id, trans_date, warehouseid AS whid, remark, 'ADJ' AS type FROM transaction_headers WHERE status != 'X' AND trans_type = 'ADJ'
                ) t
                JOIN transaction_details d ON t.id = d.trans_id
                WHERE t.whid = ? AND t.trans_date = ?
            ", [$filterWhid, $dateStr]);

            // Reset Nilai Harian
            $prod_salable_kg = 0; $prod_salable_pcs = 0;
            $prod_cull_kg_1  = 0; $prod_cull_pcs_1  = 0;
            $prod_cull_kg_2  = 0; $prod_cull_pcs_2  = 0;

            $susut_kg = 0; $susut_pcs = 0;
            $buang_kg = 0; $buang_pcs = 0;

            $jual_salable_kg = 0; $jual_salable_pcs = 0;
            $jual_cull_kg    = 0; $jual_cull_pcs    = 0;

            $adj_salable_kg  = 0; $adj_salable_pcs  = 0;
            $adj_cull_kg     = 0; $adj_cull_pcs     = 0;

            foreach ($dayTrans as $tr) {
                $is_salable  = in_array($tr->icode, $this->salable_icodes);
                $is_cull     = in_array($tr->icode, $this->cull_icodes);
                $remarkUpper = strtoupper($tr->remark ?? '');

                if ($tr->type === 'IN') {
                    if ($is_salable) {
                        $prod_salable_kg  += $tr->qty;
                        $prod_salable_pcs += $tr->qty2;
                    } elseif ($is_cull) {
                        if (str_contains($remarkUpper, 'HANCUR')) {
                            $prod_cull_kg_2  += $tr->qty;
                            $prod_cull_pcs_2 += $tr->qty2;
                        } else {
                            $prod_cull_kg_1  += $tr->qty;
                            $prod_cull_pcs_1 += $tr->qty2;
                        }
                    }
                } elseif ($tr->type === 'OUT') {
                    if (str_contains($remarkUpper, 'SUSUT')) {
                        $susut_kg  += $tr->qty;
                        $susut_pcs += $tr->qty2;
                    } elseif (str_contains($remarkUpper, 'BUANG')) {
                        $buang_kg  += $tr->qty;
                        $buang_pcs += $tr->qty2;
                    } else {
                        if ($is_salable) {
                            $jual_salable_kg  += $tr->qty;
                            $jual_salable_pcs += $tr->qty2;
                        } else {
                            $jual_cull_kg  += $tr->qty;
                            $jual_cull_pcs += $tr->qty2;
                        }
                    }
                } elseif ($tr->type === 'ADJ') {
                    if ($is_salable) {
                        $adj_salable_kg  += $tr->qty;
                        $adj_salable_pcs += $tr->qty2;
                    } elseif ($is_cull) {
                        $adj_cull_kg  += $tr->qty;
                        $adj_cull_pcs += $tr->qty2;
                    }
                }
            }

            // Saldo Awal Hari Ini (1..4)
            $c1 = $running_salable_kg;
            $c2 = $running_salable_pcs;
            $c3 = $running_cull_kg;
            $c4 = $running_cull_pcs;

            // Produksi Kandang (5..10)
            $c5 = $prod_salable_kg; $c6  = $prod_salable_pcs;
            $c7 = $prod_cull_kg_1;  $c8  = $prod_cull_pcs_1;
            $c9 = $prod_cull_kg_2;  $c10 = $prod_cull_pcs_2;

            // Total Stok (11..14)
            $c11 = $c1 + $c5;
            $c12 = $c2 + $c6;
            $c13 = $c3 + $c7 + $c9;
            $c14 = $c4 + $c8 + $c10;

            // Susut (15..16) & Buang (17..18)
            $c15 = $susut_kg; $c16 = $susut_pcs;
            $c17 = $buang_kg; $c18 = $buang_pcs;

            // Penjualan (19..22)
            $c19 = $jual_salable_kg; $c20 = $jual_salable_pcs;
            $c21 = $jual_cull_kg;    $c22 = $jual_cull_pcs;

            // Adjustment (27..30)
            $c27 = $adj_salable_kg; $c28 = $adj_salable_pcs;
            $c29 = $adj_cull_kg;    $c30 = $adj_cull_pcs;

            // Saldo Akhir (23..26)
            $c23 = $c11 - $c15 - $c19 + $c27;
            $c24 = $c12 - $c16 - $c20 + $c28;
            $c25 = $c13 - $c17 - $c21 + $c29;
            $c26 = $c14 - $c18 - $c22 + $c30;

            // Carry forward saldo akhir ke saldo awal besok
            $running_salable_kg  = $c23;
            $running_salable_pcs = $c24;
            $running_cull_kg     = $c25;
            $running_cull_pcs    = $c26;

            $this->daily_data[] = compact(
                'dayName', 'formattedDate',
                'c1','c2','c3','c4','c5','c6','c7','c8','c9','c10',
                'c11','c12','c13','c14','c15','c16','c17','c18','c19','c20',
                'c21','c22','c23','c24','c25','c26','c27','c28','c29','c30'
            );
        }
    }

    public function exportExcel()
    {
        $this->filterReport();
        $formData = $this->form->getState();
        return Excel::download(
            new DailyEggMatrixExport($this->daily_data),
            "daily-egg-matrix_{$formData['filter_from']}_{$formData['filter_to']}.xlsx",
        );
    }
}