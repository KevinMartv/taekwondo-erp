<?php

use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogoController::class, 'index'])->name('catalogo.index');
Route::get('/producto/{producto}', [CatalogoController::class, 'show'])->name('catalogo.show');

Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito', [CarritoController::class, 'store'])->name('carrito.store');
Route::patch('/carrito/{producto}', [CarritoController::class, 'update'])->name('carrito.update');
Route::delete('/carrito/{producto}', [CarritoController::class, 'destroy'])->name('carrito.destroy');

Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/compra/{venta}', [CheckoutController::class, 'show'])->name('compra.show');
