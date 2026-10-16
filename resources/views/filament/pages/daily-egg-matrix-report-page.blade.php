<x-filament-panels::page>
    <style>
        .matrix-card {
            background: #ffffff;
            padding: 12px;
            border: 1px solid #dbe3ee;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            margin-top: 15px;
        }

        .matrix-container {
            overflow-x: auto;
            max-width: 100%;
        }

        table.matrix-table {
            width: 100%;
            border-collapse: collapse !important;
            font-size: 9px;
            white-space: nowrap;
            font-family: Arial, sans-serif;
            color: #000000;
            font-variant-numeric: tabular-nums;
        }

        table.matrix-table th, 
        table.matrix-table td {
            border: 1px solid #000000 !important;
            padding: 3px 4px !important;
            text-align: right;
        }

        table.matrix-table th {
            text-align: center;
            font-weight: bold;
            vertical-align: middle;
        }

        .bg-header-main { background-color: #B4C6E7 !important; color: #000000; } 
        .bg-salable { background-color: #A9D08E !important; color: #000000; }     
        .bg-cull { background-color: #FFF2CC !important; color: #000000; }        
        .bg-yellow-num { background-color: #FFFF00 !important; color: #000000; font-weight: bold; } 

        .text-center { text-align: center !important; }
        .text-bold { font-weight: bold; }

        .matrix-table tbody tr:nth-child(even) td { background-color: #f5f8fc; }
        .matrix-table tbody tr:hover td { background-color: #eaf3f7; }
        .matrix-table tbody td:first-child { background-color: #eef3f8; font-weight: bold; }
        .matrix-table thead th { border-color: #64748b !important; }

        @media print {
            .fi-sidebar,
            .fi-topbar,
            .fi-header,
            .fi-btn,
            .fi-breadcrumbs,
            form,
            nav,
            aside,
            header,
            .print\:hidden {
                display: none !important;
            }

            body, 
            .fi-main,
            .fi-layout,
            .fi-page,
            .fi-content,
            .fi-body {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
            }

            @page {
                size: landscape;
                margin: 0.8cm;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .matrix-card {
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
            }

            .matrix-table tbody tr:nth-child(even) td,
            .matrix-table tbody tr:hover td,
            .matrix-table tbody td:first-child { background-color: #ffffff !important; }
        }
    </style>

    <form wire:submit="filterReport" class="report-filter-form space-y-6 print:hidden">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3" style="margin-top: 16px;">
            <x-filament::button type="submit" icon="heroicon-m-magnifying-glass" style="background-color: #0786a3; color: #ffffff;">
                View Report
            </x-filament::button>

            <x-filament::button color="gray" icon="heroicon-m-printer" onclick="window.print()" style="border: 1px solid #d1d5db; background-color: #ffffff; color: #1f2937;">
                Print Report
            </x-filament::button>

            <x-filament::button type="button" icon="heroicon-m-arrow-down-tray" wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" style="background-color: #0786a3; color: #ffffff;">
                Export to Excel
            </x-filament::button>
        </div>
    </form>

    @if ($hasSearched)
        <div class="matrix-card">
            <div class="matrix-container">
                <table class="matrix-table">
                <thead>
                    <tr class="bg-header-main">
                        <th rowspan="3" style="width: 70px;">Hari / Tgl</th>
                        <th colspan="4">Saldo Awal</th>
                        <th colspan="6">Produksi Kandang</th>
                        <th colspan="4">Total Stok</th>
                        <th colspan="2">Susut</th>
                        <th colspan="2">Buang</th>
                        <th colspan="4">Penjualan</th>
                        <th colspan="4">Saldo Akhir</th>
                        <th colspan="4">Adjustment</th>
                    </tr>
                    <tr>
                        <th colspan="2" class="bg-salable">SALABLE EGGS</th>
                        <th colspan="2" class="bg-cull">CULL EGGS</th>
                        <th colspan="2" class="bg-salable">SALABLE EGGS</th>
                        <th colspan="4" class="bg-cull">CULL EGGS</th>
                        <th colspan="2" class="bg-salable">SALABLE EGGS</th>
                        <th colspan="2" class="bg-cull">CULL EGGS</th>
                        <th colspan="2" class="bg-salable">SALABLE EGGS</th>
                        <th colspan="2" class="bg-cull">CULL EGGS</th>
                        <th colspan="2" class="bg-salable">SALABLE EGGS</th>
                        <th colspan="2" class="bg-cull">CULL EGGS</th>
                        <th colspan="2" class="bg-salable">SALABLE EGGS</th>
                        <th colspan="2" class="bg-cull">CULL EGGS</th>
                        <th colspan="2" class="bg-salable">SALABLE EGGS</th>
                        <th colspan="2" class="bg-cull">CULL EGGS</th>
                    </tr>
                    <tr class="bg-header-main">
                        <th>Kg</th><th>Butir</th><th>Kg</th><th>Butir</th>
                        <th>Kg</th><th>Butir</th><th>Kg</th><th>Butir</th><th>Kg</th><th>Butir</th>
                        <th>Kg</th><th>Butir</th><th>Kg</th><th>Butir</th>
                        <th>Kg</th><th>Butir</th>
                        <th>Kg</th><th>Butir</th>
                        <th>Kg</th><th>Butir</th><th>Kg</th><th>Butir</th>
                        <th>Kg</th><th>Butir</th><th>Kg</th><th>Butir</th>
                        <th>Kg</th><th>Butir</th><th>Kg</th><th>Butir</th>
                    </tr>
                    <tr class="bg-yellow-num text-center">
                        <td>-</td>
                        <td>1</td><td>2</td><td>3</td><td>4</td>
                        <td>5</td><td>6</td><td>7</td><td>8</td><td>9</td><td>10</td>
                        <td>11<br><small>1+5</small></td>
                        <td>12<br><small>2+6</small></td>
                        <td>13<br><small>3+7+9</small></td>
                        <td>14<br><small>4+8+10</small></td>
                        <td>15</td><td>16</td>
                        <td>17</td><td>18</td>
                        <td>19</td><td>20</td><td>21</td><td>22</td>
                        <td>23<br><small>11-15-19+27</small></td>
                        <td>24<br><small>12-16-20+28</small></td>
                        <td>25<br><small>13-17-21+29</small></td>
                        <td>26<br><small>14-18-22+30</small></td>
                        <td>27</td><td>28</td><td>29</td><td>30</td>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daily_data as $row)
                        <tr>
                            <td class="text-center text-bold">{{ $row['dayName'] }}<br>{{ $row['formattedDate'] }}</td>
                            <td>{{ number_format($row['c1'], 2) }}</td><td>{{ number_format($row['c2'], 0) }}</td>
                            <td>{{ number_format($row['c3'], 2) }}</td><td>{{ number_format($row['c4'], 0) }}</td>
                            <td>{{ number_format($row['c5'], 2) }}</td><td>{{ number_format($row['c6'], 0) }}</td>
                            <td>{{ number_format($row['c7'], 2) }}</td><td>{{ number_format($row['c8'], 0) }}</td>
                            <td>{{ number_format($row['c9'], 2) }}</td><td>{{ number_format($row['c10'], 0) }}</td>
                            <td class="text-bold">{{ number_format($row['c11'], 2) }}</td><td class="text-bold">{{ number_format($row['c12'], 0) }}</td>
                            <td class="text-bold">{{ number_format($row['c13'], 2) }}</td><td class="text-bold">{{ number_format($row['c14'], 0) }}</td>
                            <td>{{ number_format($row['c15'], 2) }}</td><td>{{ number_format($row['c16'], 0) }}</td>
                            <td>{{ number_format($row['c17'], 2) }}</td><td>{{ number_format($row['c18'], 0) }}</td>
                            <td>{{ number_format($row['c19'], 2) }}</td><td>{{ number_format($row['c20'], 0) }}</td>
                            <td>{{ number_format($row['c21'], 2) }}</td><td>{{ number_format($row['c22'], 0) }}</td>
                            <td class="text-bold">{{ number_format($row['c23'], 2) }}</td><td class="text-bold">{{ number_format($row['c24'], 0) }}</td>
                            <td class="text-bold">{{ number_format($row['c25'], 2) }}</td><td class="text-bold">{{ number_format($row['c26'], 0) }}</td>
                            <td>{{ number_format($row['c27'], 2) }}</td><td>{{ number_format($row['c28'], 0) }}</td>
                            <td>{{ number_format($row['c29'], 2) }}</td><td>{{ number_format($row['c30'], 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="31" class="text-center" style="padding: 15px !important;">
                                Tidak ada data transaksi mutasi telur untuk periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>