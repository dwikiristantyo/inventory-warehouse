<x-filament-panels::page>
    <form wire:submit="filterReport" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end gap-x-3">
            <x-filament::button type="submit" icon="heroicon-m-magnifying-glass">
                Tampilkan Data
            </x-filament::button>
            
            <x-filament::button color="gray" icon="heroicon-m-arrow-down-tray" tag="a" href="#">
                Export Excel / PDF
            </x-filament::button>
        </div>
    </form>

    <!-- Tempat menampilkan tabel hasil filter / ringkasan laporan -->
    <x-filament::section class="mt-6">
        <x-slot name="heading">
            Ringkasan Laporan
        </x-slot>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            Pilih filter di atas dan klik <strong>Tampilkan Data</strong> untuk melihat rincian laporan.
        </p>
    </x-filament::section>
</x-filament-panels::page>