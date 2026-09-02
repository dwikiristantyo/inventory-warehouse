<x-filament-panels::page>
    <form wire:submit="filterReport" class="space-y-4 print:hidden">
        {{ $this->form }}

        <div class="flex gap-3">
            <x-filament::button type="submit" icon="heroicon-m-magnifying-glass">
                Preview
            </x-filament::button>

            <x-filament::button color="gray" icon="heroicon-m-printer" onclick="window.print()">
                Print
            </x-filament::button>
        </div>
    </form>

    <!-- Style Khusus Mengikuti Desain Gambar 2 -->
    <style>
        .report-wrapper {
            background-color: #ffffff;
            padding: 20px;
            margin-top: 16px;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
        }

        .report-header-info {
            margin-bottom: 12px;
            font-size: 13px;
            font-weight: bold;
            line-height: 1.5;
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse !important;
            border: 1px solid #000000 !important;
            font-size: 12px;
        }

        .stock-table th, 
        .stock-table td {
            border: 1px solid #000000 !important;
            padding: 5px 8px !important;
            box-sizing: border-box;
        }

        /* Warna Header Biru sesuai Gambar Contoh */
        .stock-table thead th {
            background-color: #7cb5ec !important;
            color: #000000 !important;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .stock-table tbody td {
            background-color: #ffffff !important;
            color: #000000 !important;
        }

        /* Alignment Utility */
        .stock-table .text-center { text-align: center !important; }
        .stock-table .text-right { text-align: right !important; }
        .stock-table .text-left { text-align: left !important; }

        @media print {
            .report-wrapper {
                padding: 0;
            }
        }
    </style>

    <div class="report-wrapper">
        <!-- Info Atas -->
        <div class="report-header-info">
            <div style="font-size: 15px; text-transform: uppercase; margin-bottom: 4px;">REPORT STOCK PER GUDANG</div>
            <div>Per Tanggal: {{ $selected_to_date }}</div>
            <div>Nama Gudang: {{ $selected_warehouse_name }}</div>
        </div>

        <!-- Tabel Bergaris Hitam Lengkap -->
        <div style="overflow-x: auto;">
            <table class="stock-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 35px;">No</th>
                        <th rowspan="2" style="width: 110px;">Kode Barang</th>
                        <th colspan="4">Balance</th>
                        <th colspan="4">Incoming</th>
                        <th colspan="4">Outgoing</th>
                        <th colspan="4">Stock</th>
                    </tr>
                    <tr>
                        <!-- Balance -->
                        <th style="width: 60px;">Qty1</th>
                        <th style="width: 45px;">UOM1</th>
                        <th style="width: 60px;">Qty2</th>
                        <th style="width: 45px;">UOM2</th>
                        <!-- Incoming -->
                        <th style="width: 60px;">Qty1</th>
                        <th style="width: 45px;">UOM1</th>
                        <th style="width: 60px;">Qty2</th>
                        <th style="width: 45px;">UOM2</th>
                        <!-- Outgoing -->
                        <th style="width: 60px;">Qty1</th>
                        <th style="width: 45px;">UOM1</th>
                        <th style="width: 60px;">Qty2</th>
                        <th style="width: 45px;">UOM2</th>
                        <!-- Stock -->
                        <th style="width: 60px;">Qty1</th>
                        <th style="width: 45px;">UOM1</th>
                        <th style="width: 60px;">Qty2</th>
                        <th style="width: 45px;">UOM2</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stock_report as $index => $row)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-left" style="font-weight: bold;" title="{{ $row['description'] ?? '' }}">
                                {{ $row['item_code'] }}
                            </td>
                            
                            <!-- Balance -->
                            <td class="text-right">{{ number_format($row['bal_qty1'], 2) }}</td>
                            <td class="text-center">{{ $row['uom1'] }}</td>
                            <td class="text-right">{{ number_format($row['bal_qty2'], 0) }}</td>
                            <td class="text-center">{{ $row['uom2'] }}</td>

                            <!-- Incoming -->
                            <td class="text-right">{{ number_format($row['in_qty1'], 2) }}</td>
                            <td class="text-center">{{ $row['uom1'] }}</td>
                            <td class="text-right">{{ number_format($row['in_qty2'], 0) }}</td>
                            <td class="text-center">{{ $row['uom2'] }}</td>

                            <!-- Outgoing -->
                            <td class="text-right">{{ number_format($row['out_qty1'], 2) }}</td>
                            <td class="text-center">{{ $row['uom1'] }}</td>
                            <td class="text-right">{{ number_format($row['out_qty2'], 0) }}</td>
                            <td class="text-center">{{ $row['uom2'] }}</td>

                            <!-- Stock -->
                            <td class="text-right" style="font-weight: bold;">{{ number_format($row['stock_qty1'], 2) }}</td>
                            <td class="text-center">{{ $row['uom1'] }}</td>
                            <td class="text-right" style="font-weight: bold;">{{ number_format($row['stock_qty2'], 0) }}</td>
                            <td class="text-center">{{ $row['uom2'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="18" class="text-center" style="padding: 15px !important;">
                                Tidak ada data stok untuk gudang dan periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>