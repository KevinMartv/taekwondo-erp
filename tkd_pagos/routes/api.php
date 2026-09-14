<?php

use App\Http\Controllers\PagoController;
use Illuminate\Support\Facades\Route;

Route::post('/pagos', [PagoController::class, 'store']);
Route::patch('/pagos/{id}/confirmar', [PagoController::class, 'confirmar']);
Route::get('/alumnos/{alumno_id}/estado-cuenta', [PagoController::class, 'estadoCuenta']);
