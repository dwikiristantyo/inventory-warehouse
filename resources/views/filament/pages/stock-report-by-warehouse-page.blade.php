<x-filament-panels::page>
    <style>
        .stock-warehouse-panel,
        .stock-warehouse-table {
            width: 100%;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .stock-warehouse-panel {
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            padding: 12px;
        }

        .stock-warehouse-heading {
            color: #111827;
            font: 700 11px Arial, Helvetica, sans-serif;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .stock-warehouse-table {
            border-collapse: collapse;
        }

        .stock-warehouse-table th,
        .stock-warehouse-table td {
            border: 1px solid #cbd5e1;
            padding: 4px 5px;
        }

        .stock-warehouse-table thead th {
            background: #b4c6e7;
            color: #111827;
            font-weight: 700;
            text-align: center;
        }

        .stock-warehouse-table tbody tr:nth-child(even) { background: #f5f8fc; }
        .stock-warehouse-table tbody tr:hover { background: #eaf3f7; }
        .stock-warehouse-number { text-align: right; }

        .stock-warehouse-table th:last-child,
        .stock-warehouse-table td:last-child { font-weight: 700; }

        @media print {
            .fi-sidebar, .fi-topbar, .fi-header, .fi-breadcrumbs, form, nav, aside { display: none !important; }
            .stock-warehouse-panel { border: 0 !important; box-shadow: none !important; padding: 0 !important; }
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
        <section class="stock-warehouse-panel mt-4">
            <div class="stock-warehouse-heading">
                <div>STOCK REPORT BY WAREHOUSE</div>
                <div>Warehouse: {{ $selected_warehouse_name ?: '-' }}</div>
                <div>Period: {{ $selected_from_date ?: '-' }} - {{ $selected_to_date ?: '-' }}</div>
            </div>

            <div class="overflow-x-auto">
                <table class="stock-warehouse-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th>UOM</th>
                            <th>Opening Balance Qty</th>
                            <th>Incoming Qty</th>
                            <th>Outgoing Qty</th>
                            <th>Adjustment Qty</th>
                            <th>Ending Stock Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stock_report as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item['item_code'] }}</td>
                                <td>{{ $item['description'] }}</td>
                                <td>{{ $item['uom1'] ?: '-' }}</td>
                                <td class="stock-warehouse-number">{{ number_format($item['opening_qty'], 2) }}</td>
                                <td class="stock-warehouse-number">{{ number_format($item['incoming_qty'], 2) }}</td>
                                <td class="stock-warehouse-number">{{ number_format($item['outgoing_qty'], 2) }}</td>
                                <td class="stock-warehouse-number">{{ number_format($item['adjustment_qty'], 2) }}</td>
                                <td class="stock-warehouse-number">{{ number_format($item['ending_stock'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align: center;">No stock data found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-filament-panels::page>