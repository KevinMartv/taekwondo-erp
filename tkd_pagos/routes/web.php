<?php

use App\Http\Controllers\CheckoutTiendaController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'pagos.home')->name('pagos.home');
Route::get('/pagar', [CheckoutTiendaController::class, 'create'])->name('pagos.create');
Route::post('/pagar', [CheckoutTiendaController::class, 'store'])->name('pagos.store');
