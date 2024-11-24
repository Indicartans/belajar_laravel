<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Auth::routes();

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/product/{id}', [HomeController::class, 'detail'])->name('detail-product');
Route::post('/payment', [HomeController::class, 'payment'])->name('payment');
Route::get('/notification/{id}', [HomeController::class, 'notification'])->name('notification');
