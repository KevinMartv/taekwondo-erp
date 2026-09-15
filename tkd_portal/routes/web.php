<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Alumno;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Portal de bienvenida para visitantes (sin cuenta)
Route::view('/', 'welcome')->name('welcome');

Route::middleware(['auth'])->group(function () {

    // Semáforo de redirección según el nivel de acceso
    Route::get('/dashboard', function () {
        return Auth::user()->esAdmin()
            ? redirect()->route('admin.alumnos')
            : redirect()->route('alumno.mi_cuenta');
    })->name('dashboard');

    // 🛡️ ADMINISTRADOR: control total del alumnado y de los cobros
    Route::middleware('can:is-admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/alumnos', [Admin\AlumnoController::class, 'index'])->name('alumnos');
        Route::get('/alumnos/{alumno}/editar', [Admin\AlumnoController::class, 'edit'])->name('alumnos.edit');
        Route::put('/alumnos/{alumno}', [Admin\AlumnoController::class, 'update'])->name('alumnos.update');
        Route::patch('/alumnos/{alumno}/estado', [Admin\AlumnoController::class, 'estado'])->name('alumnos.estado');
        Route::delete('/alumnos/{alumno}', [Admin\AlumnoController::class, 'destroy'])->name('alumnos.destroy');

        Route::get('/pagos', [Admin\PagoController::class, 'index'])->name('pagos');
        Route::post('/pagos', [Admin\PagoController::class, 'store'])->name('pagos.store');
        Route::patch('/pagos/{pago}/confirmar', [Admin\PagoController::class, 'confirmar'])->name('pagos.confirmar');

        Route::view('/pos-inventario', 'admin.inventario')->name('inventario');
    });

    // 🥋 ALUMNO: sólo su propio expediente
    Route::middleware('can:is-alumno')->prefix('mi-cuenta')->name('alumno.')->group(function () {
        // Alta del expediente para cuentas que quedaron sin vincular
        Route::get('/expediente', [Alumno\ExpedienteController::class, 'create'])->name('expediente.crear');
        Route::post('/expediente', [Alumno\ExpedienteController::class, 'store'])->name('expediente.guardar');

        Route::middleware('expediente')->group(function () {
            Route::get('/', [Alumno\CuentaController::class, 'index'])->name('mi_cuenta');

            Route::get('/perfil', [Alumno\PerfilController::class, 'edit'])->name('perfil');
            Route::put('/perfil', [Alumno\PerfilController::class, 'update'])->name('perfil.update');

            Route::get('/horarios', [Alumno\HorarioController::class, 'edit'])->name('horarios');
            Route::put('/horarios', [Alumno\HorarioController::class, 'update'])->name('horarios.update');

            Route::get('/asistencias', [Alumno\AsistenciaController::class, 'index'])->name('asistencias');
            Route::post('/asistencias', [Alumno\AsistenciaController::class, 'store'])->name('asistencias.store');
            Route::delete('/asistencias/{asistencia}', [Alumno\AsistenciaController::class, 'destroy'])->name('asistencias.destroy');

            Route::post('/suscripcion/renovar', [Alumno\SuscripcionController::class, 'renovar'])->name('suscripcion.renovar');

            Route::get('/tienda', Alumno\TiendaController::class)->name('tienda');
        });

    });

    // Cuenta de acceso (correo y contraseña) — Laravel Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
