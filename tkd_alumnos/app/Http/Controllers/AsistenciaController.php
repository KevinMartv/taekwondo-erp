<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Asistencia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AsistenciaController extends Controller
{
    // Fechas de asistencia reservadas por un alumno
    public function index(Request $request, $alumnoId)
    {
        $alumno = Alumno::findOrFail($alumnoId);

        $asistencias = $alumno->asistencias()
            ->with('horario')
            ->when($request->filled('desde'), fn ($q) => $q->where('fecha', '>=', $request->input('desde')))
            ->when($request->filled('hasta'), fn ($q) => $q->where('fecha', '<=', $request->input('hasta')))
            ->orderBy('fecha')
            ->get();

        return response()->json($asistencias);
    }

    // El alumno (o el administrador) reserva una fecha de clase
    public function store(Request $request, $alumnoId)
    {
        $alumno = Alumno::findOrFail($alumnoId);

        $validated = $request->validate([
            'fecha' => [
                'required',
                'date',
                Rule::unique('asistencias')->where(fn ($q) => $q->where('alumno_id', $alumno->id)),
            ],
            'horario_id' => ['nullable', 'exists:horarios,id'],
            'estado' => ['nullable', 'in:reservada,asistio,falta'],
        ], [
            'fecha.unique' => 'Ya tienes una clase reservada en esa fecha.',
        ]);

        if (! empty($validated['horario_id']) && ! $alumno->horarios()->whereKey($validated['horario_id'])->exists()) {
            return response()->json([
                'message' => 'El horario seleccionado no está asignado a este alumno.',
            ], 422);
        }

        $asistencia = $alumno->asistencias()->create([
            'fecha' => $validated['fecha'],
            'horario_id' => $validated['horario_id'] ?? null,
            'estado' => $validated['estado'] ?? 'reservada',
        ]);

        return response()->json([
            'message' => 'Fecha de asistencia registrada',
            'asistencia' => $asistencia->load('horario'),
        ], 201);
    }

    // Cancelar una fecha reservada
    public function destroy($alumnoId, $asistenciaId)
    {
        $asistencia = Asistencia::where('alumno_id', $alumnoId)
            ->where('id', $asistenciaId)
            ->firstOrFail();

        $asistencia->delete();

        return response()->json([
            'message' => 'Fecha de asistencia cancelada',
        ]);
    }
}
