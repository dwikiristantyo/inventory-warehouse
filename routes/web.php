<?php

use Illuminate\Support\Facades\Route;
use App\Models\Warehouse;
use App\Models\Item;
use App\Models\TransactionHeader;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Di Filament v3, middleware 'filament.admin' tidak terdaftar sebagai alias route,
| sehingga diproteksi menggunakan middleware 'auth' standar Laravel.
|
*/

// Group route yang membutuhkan authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        $stats = [
            'total_warehouses' => Warehouse::count(),
            'total_items' => Item::count(),
            'total_transactions' => TransactionHeader::count(),
            'latest_warehouses' => Warehouse::with('company')->take(5)->get(),
        ];

        return view('dashboard', compact('stats'));
    })->name('dashboard');
});

// Redirect jika user mengakses /login langsung ke halaman login bawaan/custom Filament Panel
Route::get('/login', fn () => redirect()->route('filament.admin.auth.login'))->name('login');