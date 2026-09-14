<?php

use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\AsistenciaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

// Rutas de Alumnos
Route::prefix('alumnos')->group(function () {
    Route::get('/', [AlumnoController::class, 'index']);
    Route::post('/', [AlumnoController::class, 'store']);
    Route::get('/{id}', [AlumnoController::class, 'show']);
    Route::put('/{id}', [AlumnoController::class, 'update']);
    Route::delete('/{id}', [AlumnoController::class, 'destroy']);
    Route::patch('/{id}/toggle-estado', [AlumnoController::class, 'toggleEstado']);
    Route::patch('/{id}/estado', [AlumnoController::class, 'cambiarEstado']);

    // Fechas de asistencia que el alumno reserva desde su panel
    Route::get('/{alumno}/asistencias', [AsistenciaController::class, 'index']);
    Route::post('/{alumno}/asistencias', [AsistenciaController::class, 'store']);
    Route::delete('/{alumno}/asistencias/{asistencia}', [AsistenciaController::class, 'destroy']);
});

// Rutas de Catálogos (Independientes)
Route::get('/niveles', function () {
    return response()->json(DB::table('niveles')->orderBy('orden')->get());
});

Route::get('/horarios', function () {
    return response()->json(DB::table('horarios')->orderBy('id')->get());
});
