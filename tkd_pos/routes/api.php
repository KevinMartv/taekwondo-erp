<?php

use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::post('/ventas', [VentaController::class, 'store']);
Route::post('/ventas/{venta}/pagar', [VentaController::class, 'confirmarPago']);
