<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    
    // Semáforo de Redirección
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.alumnos');
        }
        return redirect()->route('alumno.mi_cuenta');
    })->name('dashboard');

    // 🛡️ RUTAS DEL ADMINISTRADOR
    Route::middleware('can:is-admin')->prefix('admin')->group(function () {
        Route::get('/alumnos', function () {
            return view('admin.alumnos');
        })->name('admin.alumnos');

        Route::get('/pagos', function () {
            return view('admin.pagos');
        })->name('admin.pagos');

        Route::get('/pos-inventario', function () {
            return view('admin.inventario');
        })->name('admin.inventario');
    });

    // 🥋 RUTAS DEL ALUMNO
    Route::middleware('can:is-alumno')->prefix('mi-cuenta')->group(function () {
        Route::get('/', function () {
            return view('alumno.estado_cuenta'); 
        })->name('alumno.mi_cuenta');
        
        Route::get('/tienda', function () {
            return redirect('http://localhost:8003'); 
        })->name('alumno.tienda');
    });

    // Rutas del Perfil de Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';    