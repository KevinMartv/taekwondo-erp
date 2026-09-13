<?php
use App\Http\Controllers\AlumnoController;
use Illuminate\Support\Facades\Route;

Route::prefix('alumnos')->group(function () {
    Route::get('/', [AlumnoController::class, 'index']);
    Route::post('/', [AlumnoController::class, 'store']);
    Route::get('/{id}', [AlumnoController::class, 'show']);
    Route::put('/{id}', [AlumnoController::class, 'update']);
    Route::patch('/{id}/toggle-estado', [AlumnoController::class, 'toggleEstado']);
});
