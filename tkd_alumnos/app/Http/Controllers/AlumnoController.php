<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Nivel;
use App\Models\Horario;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlumnoController extends Controller
{
    // Listar alumnos con filtro activo y carga de relaciones
    public function index()
    {
        $alumnos = Alumno::with(['nivel', 'horarios'])
            ->orderBy('activo', 'desc')
            ->orderBy('nombre', 'asc')
            ->get();

        return response()->json($alumnos);
    }

    // Registrar nuevo alumno
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido_paterno' => 'required|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'required|date',
            'telefono_contacto' => 'required|string|max:20',
            'nivel_id' => 'required|exists:niveles,id',
            'fecha_ingreso' => 'required|date',
            // Opcional: el alumno que se registra desde el portal elige sus horarios después
            'horarios' => 'nullable|array', // Array de IDs de horarios
            'horarios.*' => 'exists:horarios,id',
        ]);

        $alumno = Alumno::create($validated);
        $alumno->horarios()->attach($request->input('horarios', []));

        return response()->json([
            'message' => 'Alumno registrado con éxito',
            'alumno' => $alumno->load(['nivel', 'horarios'])
        ], 201);
    }

    // Mostrar un alumno específico
    public function show($id)
    {
        $alumno = Alumno::with(['nivel', 'horarios'])->findOrFail($id);
        return response()->json($alumno);
    }

    // Actualizar datos del alumno
    public function update(Request $request, $id)
    {
        $alumno = Alumno::findOrFail($id);

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:100',
            'apellido_paterno' => 'sometimes|string|max:100',
            'apellido_materno' => 'nullable|string|max:100',
            'fecha_nacimiento' => 'sometimes|date',
            'telefono_contacto' => 'sometimes|string|max:20',
            'nivel_id' => 'sometimes|exists:niveles,id',
            'fecha_ingreso' => 'sometimes|date',
            'horarios' => 'nullable|array',
            'horarios.*' => 'exists:horarios,id',
        ]);

        $alumno->update($validated);

        if ($request->has('horarios')) {
            $alumno->horarios()->sync($request->horarios);
        }

        return response()->json([
            'message' => 'Alumno actualizado correctamente',
            'alumno' => $alumno->load(['nivel', 'horarios'])
        ]);
    }

    // Baja / Alta Lógica (Cambia el estado del campo activo)
    public function toggleEstado($id)
    {
        $alumno = Alumno::findOrFail($id);
        $alumno->activo = !$alumno->activo;
        $alumno->save();

        $estado = $alumno->activo ? 'activado' : 'desactivado';

        return response()->json([
            'message' => "El alumno ha sido {$estado} correctamente",
            'activo' => $alumno->activo
        ]);
    }

    // Suspender / Activar de forma explícita (lo usa el panel de administrador)
    public function cambiarEstado(Request $request, $id)
    {
        $validated = $request->validate([
            'activo' => 'required|boolean',
        ]);

        $alumno = Alumno::findOrFail($id);
        $alumno->activo = $validated['activo'];
        $alumno->save();

        return response()->json([
            'message' => $alumno->activo
                ? 'El alumno ha sido activado correctamente'
                : 'El alumno ha sido suspendido correctamente',
            'activo' => $alumno->activo,
        ]);
    }

    // Borrado definitivo del expediente
    public function destroy($id)
    {
        $alumno = Alumno::findOrFail($id);

        // El módulo de pagos referencia alumno_id con ON DELETE RESTRICT: si hay
        // historial de cobros el expediente no se puede borrar, sólo suspender.
        // La transacción evita que un borrado rechazado deje al alumno sin horarios.
        try {
            DB::transaction(function () use ($alumno) {
                $alumno->horarios()->detach();
                $alumno->delete();
            });
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'No se puede borrar el alumno porque tiene pagos registrados. Suspéndelo en lugar de borrarlo.',
            ], 409);
        }

        return response()->json([
            'message' => 'Alumno eliminado correctamente',
        ]);
    }
}
