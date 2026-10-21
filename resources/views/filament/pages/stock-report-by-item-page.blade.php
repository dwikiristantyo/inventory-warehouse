<x-filament-panels::page>
    <style>
        .stock-item-table,
        .stock-warehouse-table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .stock-item-table th,
        .stock-item-table td,
        .stock-warehouse-table th,
        .stock-warehouse-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
        }

        .stock-item-table thead th,
        .stock-warehouse-table thead th {
            background: #b4c6e7;
            color: #111827;
            font-weight: 700;
            text-align: center;
        }

        .stock-item-table tbody tr:nth-child(even),
        .stock-warehouse-table tbody tr:nth-child(even) { background: #f5f8fc; }

        .stock-item-table tbody tr:hover,
        .stock-warehouse-table tbody tr:hover { background: #eaf3f7; }

        .stock-item-number,
        .stock-warehouse-number { text-align: right; }

        @media print {
            .fi-sidebar, .fi-topbar, .fi-header, .fi-breadcrumbs, form, nav, aside { display: none !important; }
            .stock-report-section { margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
            body, .fi-main, .fi-layout, .fi-page, .fi-content, .fi-body { background: #fff !important; }
            @page { size: landscape; margin: 0.8cm; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>

    <form wire:submit="filterReport" class="space-y-6 print:hidden">
        {{ $this->form }}

        <div class="flex flex-wrap gap-3" style="margin-top: 16px;">
            <x-filament::button type="submit" icon="heroicon-m-magnifying-glass" style="background-color: #0786a3; color: #fff;">
                View Report
            </x-filament::button>
            <x-filament::button color="gray" icon="heroicon-m-printer" onclick="window.print()" style="border: 1px solid #d1d5db; background: #fff; color: #1f2937;">
                Print Report
            </x-filament::button>
            <x-filament::button type="button" icon="heroicon-m-arrow-down-tray" wire:click="exportExcel" wire:loading.attr="disabled" wire:target="exportExcel" style="background-color: #0786a3; color: #fff;">
                Export to Excel
            </x-filament::button>
        </div>
    </form>

    @if ($hasSearched)
        <div class="stock-report-section mt-6 space-y-6">
            @forelse ($items_report as $item)
                <section>
                    <h2 class="text-[11px] font-bold uppercase text-gray-900">
                        {{ $item['item_code'] }} - {{ $item['description'] }}
                        <span class="font-normal normal-case text-gray-600">(UOM: {{ $item['uom1'] ?: '-' }})</span>
                    </h2>

                    <div class="overflow-x-auto" style="margin-top: 6px;">
                        <table class="stock-item-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Remark</th>
                                    <th>Opening Qty</th>
                                    <th>Incoming Qty</th>
                                    <th>Outgoing Qty</th>
                                    <th>Adjustment Qty</th>
                                    <th>Ending Stock Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($item['grouped'] as $date => $entries)
                                    @foreach ($entries as $entry)
                                        <tr>
                                            <td>{{ date('d-m-Y', strtotime($date)) }}</td>
                                            <td>{{ $entry['remark'] }}</td>
                                            <td class="stock-item-number">{{ number_format($entry['opening_qty'], 2) }}</td>
                                            <td class="stock-item-number">{{ number_format($entry['incoming_qty'], 2) }}</td>
                                            <td class="stock-item-number">{{ number_format($entry['outgoing_qty'], 2) }}</td>
                                            <td class="stock-item-number">{{ number_format($entry['adjustment_qty'], 2) }}</td>
                                            <td class="stock-item-number">{{ number_format($entry['ending_stock'], 2) }}</td>
                                        </tr>
                                    @endforeach

                                    @if (count($entries) > 0 && ($entries[0]['type'] ?? '') !== 'INIT')
                                        @php($dayEnding = $entries[array_key_last($entries)])
                                        <tr style="background: #fff2cc; font-weight: 700;">
                                            <td>{{ date('d-m-Y', strtotime($date)) }}</td>
                                            <td colspan="5" style="text-align: center;">DAILY ENDING STOCK</td>
                                            <td class="stock-item-number">{{ number_format($dayEnding['ending_stock'], 2) }}</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background: #eef2f7; font-weight: 700;">
                                    <td colspan="6" style="text-align: center;">ENDING STOCK</td>
                                    <td class="stock-item-number">{{ number_format($item['ending_stock'], 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>
            @empty
                <div class="rounded border border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
                    No items found for the selected filters.
                </div>
            @endforelse
        </div>
    @endif
</x-filament-panels::page>