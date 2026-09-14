<?php

namespace App\Http\Controllers\Alumno;

use App\Exceptions\ModuloException;
use App\Http\Controllers\Controller;
use App\Services\ModuloAlumnos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * El alumno edita únicamente sus propios datos: el expediente se resuelve
 * desde la sesión, nunca desde un id que llegue en la petición.
 */
class PerfilController extends Controller
{
    public function edit(Request $request, ModuloAlumnos $alumnos): View
    {
        $alumnoId = (int) $request->user()->expedienteId();

        $expediente = null;
        $avisos = [];

        try {
            $expediente = $alumnos->ver($alumnoId);
        } catch (ModuloException $e) {
            $avisos[] = $e->getMessage();
        }

        return view('alumno.perfil', [
            'expediente' => $expediente,
            'avisos' => $avisos,
        ]);
    }

    public function update(Request $request, ModuloAlumnos $alumnos): RedirectResponse
    {
        $alumnoId = (int) $request->user()->expedienteId();

        // El nivel (cinturón) y el estado de la cuenta sólo los cambia el administrador.
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'telefono_contacto' => ['required', 'string', 'max:20'],
        ]);

        try {
            $alumnos->actualizar($alumnoId, $validated);
        } catch (ModuloException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $request->user()->update([
            'name' => trim($validated['nombre'].' '.$validated['apellido_paterno']),
        ]);

        return redirect()
            ->route('alumno.perfil')
            ->with('status', 'Tus datos se actualizaron correctamente.');
    }
}
