<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Nivel;
use App\Models\Horario;
use Illuminate\Http\Request;

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
            'horarios' => 'required|array', // Array de IDs de horarios
            'horarios.*' => 'exists:horarios,id',
        ]);

        $alumno = Alumno::create($validated);
        $alumno->horarios()->attach($request->horarios);

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
}
