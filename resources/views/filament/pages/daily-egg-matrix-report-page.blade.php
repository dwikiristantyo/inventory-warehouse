<x-filament-panels::page>
    <style>
        .matrix-card {
            background: #ffffff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-top: 15px;
        }

        .matrix-container {
            overflow-x: auto;
            max-width: 100%;
        }

        table.matrix-table {
            width: 100%;
            border-collapse: collapse !important;
            font-size: 11px;
            white-space: nowrap;
            font-family: Arial, sans-serif;
            color: #000000;
        }

        table.matrix-table th, 
        table.matrix-table td {
            border: 1px solid #000000 !important;
            padding: 4px 6px !important;
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
                box-shadow: none !important;
            }
        }
    </style>

    <form wire:submit="filterReport" class="space-y-4 print:hidden">
        {{ $this->form }}

        <div class="flex gap-3 mt-3">
            <x-filament::button type="submit" icon="heroicon-m-magnifying-glass">
                Tampilkan Data
            </x-filament::button>

            <x-filament::button color="gray" icon="heroicon-m-printer" onclick="window.print()">
                Cetak Laporan
            </x-filament::button>
        </div>
    </form>

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
</x-filament-panels::page>