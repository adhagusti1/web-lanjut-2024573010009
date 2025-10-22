<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DasarBladeController;
use App\Http\Controllers\LogicController;
use App\Http\Controllers\PageController;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/dasar', [DasarBladeController::class, 'showData']);

Route::get('/logic', [LogicController::class, 'show']);

Route::get('/admin', [PageController::class, 'admin']);
Route::get('/user', [PageController::class, 'user']);
