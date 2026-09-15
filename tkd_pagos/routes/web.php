<?php

use App\Http\Controllers\CheckoutTiendaController;
use App\Http\Controllers\SuscripcionController;
use Illuminate\Support\Facades\Route;

Route::get('/pagos', function () {
    return view('index'); // Apunta a tu archivo index.blade.php
});
Route::view('/', 'pagos.home')->name('pagos.home');

// Cobro de una compra de la tienda (viene de tkd_pos)
Route::get('/pagar', [CheckoutTiendaController::class, 'create'])->name('pagos.create');
Route::post('/pagar', [CheckoutTiendaController::class, 'store'])->name('pagos.store');

// Renovación de mensualidad (viene del panel del alumno en tkd_portal)
Route::get('/suscripcion', [SuscripcionController::class, 'create'])->name('suscripcion.create');
Route::post('/suscripcion', [SuscripcionController::class, 'store'])->name('suscripcion.store');
