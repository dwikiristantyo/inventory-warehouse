<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemRequestController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect halaman utama langsung ke Filament Admin Panel
Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/request-items', [ItemRequestController::class, 'create'])
    ->name('public.item-requests.create');
Route::get('/request-items/balance', [ItemRequestController::class, 'balance'])
    ->middleware('throttle:60,1')
    ->name('public.item-requests.balance');
Route::post('/request-items', [ItemRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('public.item-requests.store');

// Redirect rute /login ke halaman login Filament Admin
Route::get('/login', fn () => redirect()->route('filament.admin.auth.login'))->name('login');