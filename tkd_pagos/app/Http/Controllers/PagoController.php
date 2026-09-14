<?php

namespace App\Http\Controllers;

use App\Models\Pago;
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
            'data' => $pago
        ], 201);
    }
    public function estadoCuenta($alumno_id)
    {
        $pagos = Pago::where('alumno_id', $alumno_id)
                     ->orderBy('periodo_cubierto', 'desc')
                     ->get();

        if ($pagos->isEmpty()) {
            return response()->json([
                'alumno_id' => $alumno_id,
                'estado_cuenta' => 'sin_historial',
                'historial' => []
            ]);
        }

        $ultimoPago = $pagos->first();
        $periodo = \Carbon\Carbon::parse($ultimoPago->periodo_cubierto);

        // Calcular cuándo le toca pagar de nuevo basado en su ciclo
        $proximoVencimiento = $ultimoPago->ciclo_pago === 'mes'
            ? $periodo->addMonth()
            : $periodo->addDays(15);

        // Si hoy es mayor a su fecha de vencimiento, debe
        $estado = now()->greaterThan($proximoVencimiento) ? 'con_adeudo' : 'al_dia';

        return response()->json([
            'alumno_id' => $alumno_id,
            'estado_cuenta' => $estado,
            'ultimo_periodo_pagado' => $ultimoPago->periodo_cubierto,
            'proximo_vencimiento' => $proximoVencimiento->toDateString(),
            'historial' => $pagos
        ]);
    }
}