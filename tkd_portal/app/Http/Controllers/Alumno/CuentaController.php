<?php

namespace App\Http\Controllers\Alumno;

use App\Exceptions\ModuloException;
use App\Http\Controllers\Controller;
use App\Services\ModuloAlumnos;
use App\Services\ModuloPagos;
use App\Services\ModuloTienda;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CuentaController extends Controller
{
    /**
     * Panel del alumno: reúne su expediente (tkd_alumnos), la vigencia de su
     * mensualidad (tkd_pagos) y el acceso firmado a la tienda (tkd_pos).
     */
    public function index(
        Request $request,
        ModuloAlumnos $alumnos,
        ModuloPagos $pagos,
        ModuloTienda $tienda,
    ): View {
        $alumnoId = (int) $request->user()->expedienteId();

        $expediente = null;
        $asistencias = [];
        $avisos = $this->resultadoRenovacion($request);

        try {
            $expediente = $alumnos->ver($alumnoId);
            $asistencias = collect($alumnos->asistencias($alumnoId))
                ->filter(fn ($a) => Carbon::parse($a['fecha'])->gte(Carbon::today()))
                ->take(5)
                ->values()
                ->all();
        } catch (ModuloException $e) {
            $avisos[] = $e->getMessage();
        }

        $suscripcion = null;

        try {
            $suscripcion = $pagos->estadoCuenta($alumnoId);
        } catch (ModuloException $e) {
            $avisos[] = $e->getMessage();
        }

        $nombre = $expediente
            ? trim(($expediente['nombre'] ?? '').' '.($expediente['apellido_paterno'] ?? ''))
            : $request->user()->name;

        return view('alumno.mi_cuenta', [
            'expediente' => $expediente,
            'suscripcion' => $suscripcion,
            'asistencias' => $asistencias,
            'avisos' => $avisos,
            'urlTienda' => $tienda->url($alumnoId, $nombre),
        ]);
    }

    /**
     * El módulo de pagos regresa al alumno con ?suscripcion=ok|pendiente|duplicada.
     *
     * @return array<int, string>
     */
    private function resultadoRenovacion(Request $request): array
    {
        return match ($request->query('suscripcion')) {
            'ok' => ['Tu pago se registró: la vigencia de tu mensualidad ya está actualizada.'],
            'pendiente' => ['Registramos tu renovación en efectivo. Queda pendiente hasta que el administrador la confirme en recepción.'],
            'duplicada' => ['Ese periodo ya estaba pagado, no generamos un cobro nuevo.'],
            default => [],
        };
    }
}
