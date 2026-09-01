<style>
    /* Sembunyikan brand logo bawaan Filament (teks "Laravel") */
    .fi-logo {
        display: none !important;
    }
</style>

@php
    $user = auth()->user();
    $companies = method_exists($user, 'companies') ? $user->companies : collect();
    $companyName = $companies->first()->company_name ?? 'WH Warehouse System';
@endphp

<div class="flex items-center gap-x-3 px-3 py-2">
    <!-- Gambar Logo (Opsional jika ada) -->
    <!-- <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto" /> -->

    <div class="flex flex-col">
        <span class="text-sm font-bold leading-tight text-gray-950 dark:text-white">
            
        </span>
        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
            {{ $companyName }}
        </span>
    </div>
</div>