<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect halaman utama langsung ke Filament Admin Panel
Route::get('/', function () {
    return redirect('/admin');
});

// Redirect rute /login ke halaman login Filament Admin
Route::get('/login', fn () => redirect()->route('filament.admin.auth.login'))->name('login');