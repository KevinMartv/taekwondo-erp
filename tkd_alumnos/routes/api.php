<?php

use App\Http\Controllers\AlumnoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Rutas de Alumnos
Route::prefix('alumnos')->group(function () {
    Route::get('/', [AlumnoController::class, 'index']);
    Route::post('/', [AlumnoController::class, 'store']);
    Route::get('/{id}', [AlumnoController::class, 'show']);
    Route::put('/{id}', [AlumnoController::class, 'update']);
    Route::patch('/{id}/toggle-estado', [AlumnoController::class, 'toggleEstado']);
});

// Rutas de Catálogos (Independientes)
Route::get('/niveles', function () {
    return response()->json(DB::table('niveles')->orderBy('orden')->get());
});

Route::get('/horarios', function () {
    return response()->json(DB::table('horarios')->orderBy('id')->get());
});