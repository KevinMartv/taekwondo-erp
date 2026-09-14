<?php

namespace App\Http\Controllers\Alumno;

use App\Exceptions\ModuloException;
use App\Http\Controllers\Controller;
use App\Services\ModuloAlumnos;
use App\Services\ModuloPagos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SuscripcionController extends Controller
{
    /**
     * Renovación de la mensualidad: el portal calcula el periodo que toca
     * cubrir y manda al alumno a la pasarela simulada de tkd_pagos.
     */
    public function renovar(Request $request, ModuloAlumnos $alumnos, ModuloPagos $pagos): RedirectResponse
    {
        $alumnoId = (int) $request->user()->expedienteId();

        try {
            $expediente = $alumnos->ver($alumnoId);
            $estado = $pagos->estadoCuenta($alumnoId);
        } catch (ModuloException $e) {
            return back()->with('error', $e->getMessage());
        }

        if (! ($expediente['activo'] ?? true)) {
            return back()->with('error', 'Tu cuenta está suspendida: acude con el administrador para reactivarla.');
        }

        $periodo = $estado['periodo_sugerido'] ?? Carbon::today()->startOfMonth()->toDateString();
        $monto = (float) ($estado['monto_mensualidad'] ?? config('services.suscripcion.monto'));

        $nombre = trim(($expediente['nombre'] ?? '').' '.($expediente['apellido_paterno'] ?? ''));

        return redirect()->away($pagos->urlRenovacion(
            $alumnoId,
            $nombre !== '' ? $nombre : $request->user()->name,
            $periodo,
            $monto,
            route('alumno.mi_cuenta'),
        ));
    }
}
