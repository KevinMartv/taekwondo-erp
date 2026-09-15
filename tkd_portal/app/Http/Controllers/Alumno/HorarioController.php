<?php

namespace App\Http\Controllers\Alumno;

use App\Exceptions\ModuloException;
use App\Http\Controllers\Controller;
use App\Services\ModuloAlumnos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HorarioController extends Controller
{
    public function edit(Request $request, ModuloAlumnos $alumnos): View
    {
        $alumnoId = (int) $request->user()->expedienteId();

        $horarios = [];
        $seleccionados = [];
        $avisos = [];

        try {
            $horarios = $alumnos->horarios();
            $expediente = $alumnos->ver($alumnoId);
            $seleccionados = collect($expediente['horarios'] ?? [])->pluck('id')->all();
        } catch (ModuloException $e) {
            $avisos[] = $e->getMessage();
        }

        return view('alumno.horarios', [
            'horarios' => $horarios,
            'seleccionados' => $seleccionados,
            'avisos' => $avisos,
        ]);
    }

    public function update(Request $request, ModuloAlumnos $alumnos): RedirectResponse
    {
        $alumnoId = (int) $request->user()->expedienteId();

        $validated = $request->validate([
            'horarios' => ['array'],
            'horarios.*' => ['integer'],
        ]);

        try {
            $alumnos->actualizar($alumnoId, [
                'horarios' => $validated['horarios'] ?? [],
            ]);
        } catch (ModuloException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('alumno.horarios')
            ->with('status', 'Tus horarios de entrenamiento quedaron guardados.');
    }
}
