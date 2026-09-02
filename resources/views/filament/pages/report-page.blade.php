<x-filament-panels::page>
    <form wire:submit="filterReport" class="space-y-6">
        {{ $this->form }}

        <div class="flex gap-3">
            <x-filament::button type="submit" icon="heroicon-m-magnifying-glass">
                Tampilkan Data
            </x-filament::button>

            <x-filament::button color="gray" icon="heroicon-m-printer" onclick="window.print()">
                Cetak Laporan
            </x-filament::button>
        </div>
    </form>

    <div class="mt-6 space-y-8">
        @forelse ($items_report as $item)
            @php
                // Mengambil UOM secara dinamis dari master item (dengan fallback jika kosong)
                $uom1 = $item['uom1'] ?? 'Qty 1';
                $uom2 = $item['uom2'] ?? 'Qty 2';
            @endphp

            <div class="space-y-2">
                <!-- Header Nama Barang & UOM Dinamis -->
                <h3 class="text-xs font-bold text-gray-900 uppercase">
                    {{ $item['icode'] }} - {{ $item['desc1'] }} 
                    <span class="text-xs font-normal text-gray-600 lowercase">
                        (UOM: {{ $uom1 }} {{ !empty($item['uom2']) ? '& ' . $uom2 : '' }})
                    </span>
                </h3>

                <div class="overflow-x-auto">
                    <!-- Tabel: Font seragam monospace, ukuran diperkecil (11px) -->
                    <table class="w-full text-[11px] font-mono text-gray-900" style="border-collapse: collapse; border: 1px solid #000 !important;">
                        <thead>
                            <tr style="background-color: #93c5fd; font-weight: bold; color: #000;">
                                <th rowspan="2" style="border: 1px solid #000 !important; padding: 4px; text-align: center; width: 85px;">Tanggal</th>
                                <th rowspan="2" style="border: 1px solid #000 !important; padding: 4px; text-align: left;">Keterangan</th>
                                <th colspan="2" style="border: 1px solid #000 !important; padding: 3px; text-align: center;">Saldo Awal</th>
                                <th colspan="2" style="border: 1px solid #000 !important; padding: 3px; text-align: center;">Masuk (IN)</th>
                                <th colspan="2" style="border: 1px solid #000 !important; padding: 3px; text-align: center;">Keluar (OUT)</th>
                                <th colspan="2" style="border: 1px solid #000 !important; padding: 3px; text-align: center;">Adjustment</th>
                                <th colspan="2" style="border: 1px solid #000 !important; padding: 3px; text-align: center;">Saldo Akhir</th>
                            </tr>
                            <!-- Header Sub-Kolom UOM Dinamis -->
                            <tr style="background-color: #93c5fd; font-weight: bold; color: #000;">
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
                                            {{ number_format($row['beg_kg'] ?? 0, 2) }}
                                        </td>
                                        <td style="border: 1px solid #000 !important; padding: 3px 5px; text-align: right;">
                                            {{ number_format($row['beg_pcs'] ?? 0, 0) }}
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
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold; background-color: #fff;">
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
</x-filament-panels::page>