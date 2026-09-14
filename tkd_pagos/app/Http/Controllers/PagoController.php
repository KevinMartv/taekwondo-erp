<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PagoController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'alumno_id' => ['required', 'integer', Rule::exists('alumnos', 'id')],
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:efectivo,transferencia',
            'numero_rastreo' => 'required_if:metodo_pago,transferencia|string|nullable',
            'ciclo_pago' => 'required|in:mes,quincena',
            'periodo_cubierto' => 'required|date',
            'fecha_pago' => 'required|date',
            ]);

        $validated['estado'] = 'pagado';

        $pago = Pago::create($validated);

        return response()->json([
            'mensaje' => 'Pago registrado correctamente',
            'data' => $pago,
        ], 201);
    }

    /**
     * Estado de la suscripción de un alumno: es la fuente de verdad del
     * indicador "ya pagó / no ha pagado" que muestra el portal.
     */
    public function estadoCuenta($alumno_id)
    {
        $pagos = Pago::where('alumno_id', $alumno_id)
            ->orderBy('periodo_cubierto', 'desc')
            ->get();

        $mensualidad = (float) config('services.suscripcion.monto');

        // Sólo los pagos liquidados extienden la vigencia de la suscripción.
        $liquidados = $pagos->where('estado', 'pagado');
        $pendientes = $pagos->whereIn('estado', ['pendiente', 'atrasado'])->values();

        if ($liquidados->isEmpty()) {
            return response()->json([
                'alumno_id' => (int) $alumno_id,
                'estado_cuenta' => 'sin_historial',
                'vigente' => false,
                'por_vencer' => false,
                'ultimo_periodo_pagado' => null,
                'proximo_vencimiento' => null,
                'dias_restantes' => null,
                'monto_mensualidad' => $mensualidad,
                'periodo_sugerido' => Carbon::today()->startOfMonth()->toDateString(),
                'pagos_pendientes' => $pendientes,
                'historial' => $pagos,
            ]);
        }

        $ultimoPago = $liquidados->first();
        $periodo = Carbon::parse($ultimoPago->periodo_cubierto)->startOfDay();

        $proximoVencimiento = $ultimoPago->ciclo_pago === 'mes'
            ? $periodo->copy()->addMonth()
            : $periodo->copy()->addDays(15);

        $hoy = Carbon::today();
        $diasRestantes = (int) round($hoy->diffInDays($proximoVencimiento, false));
        $vigente = $diasRestantes >= 0;

        return response()->json([
            'alumno_id' => (int) $alumno_id,
            'estado_cuenta' => $vigente ? 'al_dia' : 'con_adeudo',
            'vigente' => $vigente,
            'por_vencer' => $vigente && $diasRestantes <= 5,
            'ultimo_periodo_pagado' => $periodo->toDateString(),
            'proximo_vencimiento' => $proximoVencimiento->toDateString(),
            'dias_restantes' => $diasRestantes,
            'monto_mensualidad' => $mensualidad,
            'periodo_sugerido' => $proximoVencimiento->toDateString(),
            'pagos_pendientes' => $pendientes,
            'historial' => $pagos,
        ]);
    }

    /**
     * El administrador confirma un cobro que quedó pendiente (por ejemplo,
     * una renovación que el alumno eligió pagar en efectivo).
     */
    public function confirmar($id)
    {
        $pago = Pago::findOrFail($id);

        $pago->estado = 'pagado';
        $pago->fecha_pago = $pago->fecha_pago ?: Carbon::today()->toDateString();
        $pago->save();

        return response()->json([
            'mensaje' => 'Pago confirmado correctamente',
            'data' => $pago,
        ]);
    }
}
