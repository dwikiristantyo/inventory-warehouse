<x-filament-panels::page>
    <style>
        .mutation-table {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            font-variant-numeric: tabular-nums;
        }

        .mutation-table tbody tr:nth-child(even) td { background-color: #f5f8fc; }
        .mutation-table tbody tr:hover td { background-color: #eaf3f7; }
        .mutation-table th { font-size: 9px; }
        .mutation-table td { line-height: 1.25; }

        /* Rule Khusus Mode Print */
        @media print {
            /* 1. Sembunyikan elemen UI Filament, navigasi, dan Form Filter */
            .fi-sidebar,
            .fi-topbar,
            .fi-header,
            .fi-btn,
            .fi-breadcrumbs,
            form,
            nav,
            aside,
            header {
                display: none !important;
            }

            /* 2. Reset layout kontainer utama agar tidak terpotong */
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

            /* 3. Atur orientasi cetak dan paksa warna latar belakang header tabel tercetak */
            @page {
                size: landscape; /* Opsi landscape agar tabel lebar tercetak rapi */
                margin: 1cm;
            }

            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            /* 4. Mencegah blok tabel terpotong secara tidak rapi di tengah halaman */
            .space-y-2 {
                page-break-inside: avoid;
                margin-bottom: 1.5rem !important;
            }
        }
    </style>

    <form wire:submit="filterReport" class="report-filter-form space-y-6">
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
        <div class="mt-6 space-y-8">
            @forelse ($items_report as $item)
            @php
                // Mengambil UOM secara dinamis dari master item (dengan fallback jika kosong)
                $uom1 = $item['uom1'] ?? 'Qty 1';
                $uom2 = $item['uom2'] ?? 'Qty 2';
            @endphp

            <div class="space-y-2">
                <!-- Header Nama Barang & UOM Dinamis -->
                <h3 class="text-[11px] font-bold text-gray-900 uppercase">
                    {{ $item['icode'] }} - {{ $item['desc1'] }} 
                    <span class="text-xs font-normal text-gray-600 lowercase">
                        (UOM: {{ $uom1 }} {{ !empty($item['uom2']) ? '& ' . $uom2 : '' }})
                    </span>
                </h3>

                <div class="overflow-x-auto" style="margin-top: 6px;">
                    <!-- Tabel: Font seragam monospace, ukuran diperkecil (11px) -->
                    <table class="mutation-table w-full text-gray-900" style="border-collapse: collapse; border: 1px solid #000 !important;">
                        <thead>
                            <tr style="background-color: #B4C6E7; font-weight: bold; color: #000;">
                                <th rowspan="2" style="border: 1px solid #000 !important; padding: 4px; text-align: center; width: 85px;">Tanggal</th>
                                <th rowspan="2" style="border: 1px solid #000 !important; padding: 4px; text-align: left;">Keterangan</th>
                                <th colspan="2" style="border: 1px solid #000 !important; padding: 3px; text-align: center;">Saldo Awal</th>
                                <th colspan="2" style="border: 1px solid #000 !important; padding: 3px; text-align: center;">Masuk (IN)</th>
                                <th colspan="2" style="border: 1px solid #000 !important; padding: 3px; text-align: center;">Keluar (OUT)</th>
                                <th colspan="2" style="border: 1px solid #000 !important; padding: 3px; text-align: center;">Adjustment</th>
                                <th colspan="2" style="border: 1px solid #000 !important; padding: 3px; text-align: center;">Saldo Akhir</th>
                            </tr>
                            <!-- Header Sub-Kolom UOM Dinamis -->
                            <tr style="background-color: #B4C6E7; font-weight: bold; color: #000;">
                                <th style="border: 1px solid #000 !important; padding: 3px; text-align: right; width: 65px;">{{ $uom1 }}</th>
                                <th style="border: 1px solid #000 !important; padding: 3px; text-align: right; width: 65px;">{{ $uom2 }}</th>
                                <th style="border: 1px solid #000 !important; padding: 3px; text-align: right; width: 65px;">{{ $uom1 }}</th>
                                <th style="border: 1px solid #000 !important; padding: 3px; text-align: right; width: 65px;">{{ $uom2 }}</th>
                                <th style="border: 1px solid #000 !important; padding: 3px; text-align: right; width: 65px;">{{ $uom1 }}</th>
                                <th style="border: 1px solid #000 !important; padding: 3px; text-align: right; width: 65px;">{{ $uom2 }}</th>
                                <th style="border: 1px solid #000 !important; padding: 3px; text-align: right; width: 65px;">{{ $uom1 }}</th>
                                <th style="border: 1px solid #000 !important; padding: 3px; text-align: right; width: 65px;">{{ $uom2 }}</th>
                                <th style="border: 1px solid #000 !important; padding: 3px; text-align: right; width: 65px;">{{ $uom1 }}</th>
                                <th style="border: 1px solid #000 !important; padding: 3px; text-align: right; width: 65px;">{{ $uom2 }}</th>
                            </tr>
                        </thead>
                        <tbody style="background-color: #fff;">
                            @foreach ($item['grouped'] as $date => $rows)
                                @foreach ($rows as $row)
                                    <tr>
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: center; white-space: nowrap;">
                                            {{ date('d-m-Y', strtotime($date)) }}
                                        </td>
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px;">
                                            {{ $row['remark'] }}
                                        </td>
                                        <!-- Saldo Awal -->
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['bal_kg'] - ($row['in_kg'] - $row['out_kg'] + $row['adj_kg']), 2) }}
                                        </td>
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['bal_pcs'] - ($row['in_pcs'] - $row['out_pcs'] + $row['adj_pcs']), 0) }}
                                        </td>
                                        <!-- Masuk (IN) -->
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['in_kg'], 2) }}
                                        </td>
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['in_pcs'], 0) }}
                                        </td>
                                        <!-- Keluar (OUT) -->
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['out_kg'], 2) }}
                                        </td>
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['out_pcs'], 0) }}
                                        </td>
                                        <!-- Adjustment -->
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['adj_kg'], 2) }}
                                        </td>
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['adj_pcs'], 0) }}
                                        </td>
                                        <!-- Saldo Akhir -->
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['bal_kg'], 2) }}
                                        </td>
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['bal_pcs'], 0) }}
                                        </td>
                                    </tr>
                                @endforeach
                                @if (count($rows) > 0 && ($rows[0]['type'] ?? '') !== 'INIT')
                                    @php($dayEnding = $rows[array_key_last($rows)])
                                    <tr style="background-color: #FFFF00; font-weight: bold;">
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: center; white-space: nowrap;">
                                            {{ date('d-m-Y', strtotime($date)) }}
                                        </td>
                                        <td colspan="9" style="border: 1px solid #000 !important; padding: 3px 5px; text-align: center;">DAILY CLOSING BALANCE</td>
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">{{ number_format($dayEnding['bal_kg'], 2) }}</td>
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">{{ number_format($dayEnding['bal_pcs'], 0) }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background-color: #eef2f7;">
                                <td colspan="10" style="border: 1px solid #000 !important; padding: 4px 5px; text-align: center; text-transform: uppercase;">
                                    TOTAL SALDO AKHIR
                                </td>
                                <td style="border: 1px solid #000 !important; padding: 4px 5px; text-align: right;">
                                    {{ number_format($item['final_kg'], 2) }}
                                </td>
                                <td style="border: 1px solid #000 !important; padding: 4px 5px; text-align: right;">
                                    {{ number_format($item['final_pcs'], 0) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @empty
                <div class="p-6 text-center text-gray-500 bg-white rounded-xl border border-gray-200">
                    Tidak ada data transaksi untuk filter yang dipilih.
                </div>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>