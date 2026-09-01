<x-filament-panels::page>
    <style>
        .wh-grid { display: grid; grid-template-columns: repeat(1, minmax(0, 1fr)); gap: 1rem; }
        @media (min-width: 768px) { .wh-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        .wh-card { background-color: #1f2937; border: 1px solid #374151; border-radius: 0.75rem; padding: 1.5rem; color: #fff; }
        .wh-title { font-size: 0.875rem; color: #9ca3af; font-weight: 500; }
        .wh-value { font-size: 1.875rem; font-weight: 700; margin-top: 0.5rem; }
        .wh-table { width: 100%; text-align: left; font-size: 0.875rem; border-collapse: collapse; }
        .wh-table th { background-color: #374151; padding: 0.75rem 1rem; text-transform: uppercase; font-size: 0.75rem; color: #d1d5db; }
        .wh-table td { padding: 0.75rem 1rem; border-bottom: 1px solid #374151; }
        .wh-badge { display: inline-flex; padding: 0.25rem 0.5rem; font-size: 0.75rem; font-weight: 600; border-radius: 0.375rem; background-color: rgba(16, 185, 129, 0.2); color: #34d399; }
    </style>

    <div style="display: flex; flex-direction: column; gap: 1.5rem; max-width: 100%;">
        
        <!-- Section Top Cards Stats -->
        <div class="wh-grid">
            <div class="wh-card">
                <div class="wh-title">Total Gudang</div>
                <div class="wh-value">
                    {{ number_format($stats['total_warehouses'] ?? 0) }}
                </div>
            </div>

            <div class="wh-card">
                <div class="wh-title">Total Item Barang</div>
                <div class="wh-value">
                    {{ number_format($stats['total_items'] ?? 0) }}
                </div>
            </div>

            <div class="wh-card">
                <div class="wh-title">Total Transaksi</div>
                <div class="wh-value">
                    {{ number_format($stats['total_transactions'] ?? 0) }}
                </div>
            </div>
        </div>

        <!-- Section Table 5 Gudang Terbaru -->
        <div class="wh-card">
            <h3 style="font-size: 1rem; font-weight: 600; margin-top: 0; margin-bottom: 1rem;">
                5 Gudang Terbaru
            </h3>

            <div style="overflow-x: auto;">
                <table class="wh-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Gudang</th>
                            <th>Perusahaan</th>
                            <th>Telepon</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stats['latest_warehouses'] ?? [] as $warehouse)
                            <tr>
                                <td style="font-weight: 500;">
                                    {{ $warehouse->warehouseid }}
                                </td>
                                <td style="font-weight: 700;">
                                    {{ $warehouse->warehouse_name }}
                                </td>
                                <td>
                                    {{ $warehouse->company->company_name ?? '-' }}
                                </td>
                                <td>
                                    {{ $warehouse->phone ?? '-' }}
                                </td>
                                <td>
                                    <span class="wh-badge">
                                        {{ strtoupper($warehouse->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; color: #9ca3af;">
                                    Belum ada data gudang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-filament-panels::page>