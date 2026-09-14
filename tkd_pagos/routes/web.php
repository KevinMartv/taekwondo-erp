<?php

use App\Http\Controllers\CheckoutTiendaController;
use Illuminate\Support\Facades\Route;

Route::get('/pagos', function () {
    return view('index'); // Apunta a tu archivo index.blade.php
});
Route::view('/', 'pagos.home')->name('pagos.home');
Route::get('/pagar', [CheckoutTiendaController::class, 'create'])->name('pagos.create');
Route::post('/pagar', [CheckoutTiendaController::class, 'store'])->name('pagos.store');
