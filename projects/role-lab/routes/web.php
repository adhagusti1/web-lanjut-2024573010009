<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute yang dapat diakses oleh semua pengguna terautentikasi
    Route::get('/all', function () {
        return view('all');
    });

    // Rute khusus admin dengan middleware role
    Route::get('/admin', function () {
        return view('admin');
    })->middleware('role:admin');

    // Rute khusus manager dengan middleware role
    Route::get('/manager', function () {
        return view('manager');
    })->middleware('role:manager');

    // Rute khusus user dengan middleware role
    Route::get('/user', function () {
        return view('user');
    })->middleware('role:user');
});

require __DIR__.'/auth.php';
