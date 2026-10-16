<x-filament-panels::page>
    <!-- Style Khusus Mengikuti Desain dan Mode Cetak (Print) -->
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
            border: 1px solid #94a3b8 !important;
            font-size: 9px;
            font-variant-numeric: tabular-nums;
        }

        .stock-table th, 
        .stock-table td {
            border: 1px solid #cbd5e1 !important;
            padding: 3px 4px !important;
            box-sizing: border-box;
        }

        /* Header colors follow the report reference palette. */
        .stock-table thead th {
            background-color: #B4C6E7 !important;
            color: #000000 !important;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .stock-table thead tr:nth-child(2) th { background-color: #D9E2F3 !important; }

        .stock-table tbody td {
            background-color: #ffffff !important;
            color: #000000 !important;
        }

        .stock-table tbody tr:nth-child(even) td { background-color: #f5f8fc !important; }
        .stock-table tbody tr:hover td { background-color: #eaf3f7 !important; }
        .stock-table tbody td:nth-child(15),
        .stock-table tbody td:nth-child(17) { font-weight: bold; }

        /* Alignment Utility */
        .stock-table .text-center { text-align: center !important; }
        .stock-table .text-right { text-align: right !important; }
        .stock-table .text-left { text-align: left !important; }

        /* Aturan Cetak / Print */
        @media print {
            /* 1. Sembunyikan elemen navigasi, topbar, sidebar, dan form filter */
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

            /* 2. Reset Layout Halaman Cetak */
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

            /* 3. Pengaturan Orientasi Landscape dan Ukuran Cetak */
            @page {
                size: landscape;
                margin: 1cm;
            }

            /* 4. Paksa Printer Mencetak Warna Latar Header Tabel */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .report-wrapper {
                padding: 0 !important;
                margin-top: 0 !important;
            }

            .stock-table {
                width: 100% !important;
            }

            .stock-table tbody tr:nth-child(even) td,
            .stock-table tbody tr:hover td { background-color: #ffffff !important; }
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
        <div class="report-wrapper">
        <!-- Info Atas Laporan -->
        <div class="report-header-info">
            <div style="font-size: 15px; text-transform: uppercase; margin-bottom: 4px;">REPORT STOCK PER GUDANG</div>
            <div>Per Tanggal: {{ $selected_to_date ? date('d/m/Y', strtotime($selected_to_date)) : '-' }}</div>
            <div>Nama Gudang: {{ $selected_warehouse_name ?? '-' }}</div>
        </div>

        <!-- Tabel Stock Per Gudang -->
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
    @endif
</x-filament-panels::page>