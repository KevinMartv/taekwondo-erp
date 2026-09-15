<?php

namespace App\Http\Controllers\Alumno;

use App\Exceptions\ModuloException;
use App\Http\Controllers\Controller;
use App\Services\ModuloAlumnos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Alta del expediente para cuentas de alumno que quedaron sin vincular.
 */
class ExpedienteController extends Controller
{
    public function create(Request $request, ModuloAlumnos $alumnos): View|RedirectResponse
    {
        if ($request->user()->expedienteId()) {
            return redirect()->route('alumno.mi_cuenta');
        }

        $niveles = [];
        $avisos = [];

        try {
            $niveles = $alumnos->niveles();
        } catch (ModuloException $e) {
            $avisos[] = $e->getMessage();
        }

        return view('alumno.expediente', [
            'niveles' => $niveles,
            'avisos' => $avisos,
        ]);
    }

    public function store(Request $request, ModuloAlumnos $alumnos): RedirectResponse
    {
        if ($request->user()->expedienteId()) {
            return redirect()->route('alumno.mi_cuenta');
        }

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'telefono_contacto' => ['required', 'string', 'max:20'],
            'nivel_id' => ['required', 'integer'],
        ]);

        try {
            $expediente = $alumnos->crear($validated + [
                'fecha_ingreso' => Carbon::today()->toDateString(),
            ]);
        } catch (ModuloException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $request->user()->update(['alumno_id' => $expediente['id'] ?? null]);

        return redirect()
            ->route('alumno.mi_cuenta')
            ->with('status', 'Tu expediente quedó registrado. Ya puedes elegir tus horarios.');
    }
}
