<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ModuloException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ModuloAlumnos;
use App\Services\ModuloPagos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestión completa del alumnado: el administrador ve el expediente
 * (tkd_alumnos) junto con el indicador de pago (tkd_pagos) y puede editar,
 * suspender, activar o borrar.
 */
class AlumnoController extends Controller
{
    public function index(Request $request, ModuloAlumnos $alumnos, ModuloPagos $pagos): View
    {
        $listado = [];
        $avisos = [];

        try {
            $listado = $alumnos->lista();
        } catch (ModuloException $e) {
            $avisos[] = $e->getMessage();
        }

        $filtro = (string) $request->query('estado', 'todos');
        $busqueda = trim((string) $request->query('q', ''));

        $alumnado = collect($listado)
            ->map(function (array $alumno) use ($pagos) {
                $alumno['suscripcion'] = $pagos->estadoCuentaSeguro((int) $alumno['id']);

                return $alumno;
            })
            ->when($busqueda !== '', fn ($items) => $items->filter(
                fn ($alumno) => str_contains(
                    mb_strtolower(implode(' ', [
                        $alumno['nombre'] ?? '',
                        $alumno['apellido_paterno'] ?? '',
                        $alumno['apellido_materno'] ?? '',
                        $alumno['id'] ?? '',
                    ])),
                    mb_strtolower($busqueda),
                ),
            ))
            ->when($filtro === 'activos', fn ($items) => $items->filter(fn ($a) => (bool) ($a['activo'] ?? false)))
            ->when($filtro === 'suspendidos', fn ($items) => $items->reject(fn ($a) => (bool) ($a['activo'] ?? false)))
            ->when($filtro === 'con_adeudo', fn ($items) => $items->filter(
                fn ($a) => ($a['suscripcion']['estado_cuenta'] ?? '') !== 'al_dia',
            ))
            ->values();

        return view('admin.alumnos', [
            'alumnado' => $alumnado,
            'filtro' => $filtro,
            'busqueda' => $busqueda,
            'avisos' => $avisos,
            'resumen' => [
                'total' => $alumnado->count(),
                'activos' => $alumnado->where('activo', true)->count(),
                'al_dia' => $alumnado->filter(fn ($a) => ($a['suscripcion']['estado_cuenta'] ?? '') === 'al_dia')->count(),
            ],
        ]);
    }

    public function edit(ModuloAlumnos $alumnos, ModuloPagos $pagos, int $alumno): View|RedirectResponse
    {
        try {
            $expediente = $alumnos->ver($alumno);
            $niveles = $alumnos->niveles();
            $horarios = $alumnos->horarios();
        } catch (ModuloException $e) {
            return redirect()->route('admin.alumnos')->with('error', $e->getMessage());
        }

        return view('admin.alumno_editar', [
            'expediente' => $expediente,
            'niveles' => $niveles,
            'horarios' => $horarios,
            'seleccionados' => collect($expediente['horarios'] ?? [])->pluck('id')->all(),
            'suscripcion' => $pagos->estadoCuentaSeguro($alumno),
            'cuenta' => User::where('alumno_id', $alumno)->first(),
        ]);
    }

    public function update(Request $request, ModuloAlumnos $alumnos, int $alumno): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['required', 'date', 'before:today'],
            'telefono_contacto' => ['required', 'string', 'max:20'],
            'nivel_id' => ['required', 'integer'],
            'fecha_ingreso' => ['required', 'date'],
            'horarios' => ['array'],
            'horarios.*' => ['integer'],
        ]);

        try {
            $alumnos->actualizar($alumno, $validated + ['horarios' => $validated['horarios'] ?? []]);
        } catch (ModuloException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.alumnos')
            ->with('status', 'Expediente del alumno #'.$alumno.' actualizado.');
    }

    /**
     * Suspender o activar según el indicador de pago de la suscripción.
     */
    public function estado(Request $request, ModuloAlumnos $alumnos, int $alumno): RedirectResponse
    {
        $validated = $request->validate([
            'activo' => ['required', 'boolean'],
        ]);

        try {
            $mensaje = $alumnos->cambiarEstado($alumno, (bool) $validated['activo']);
        } catch (ModuloException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', $mensaje);
    }

    public function destroy(ModuloAlumnos $alumnos, ModuloPagos $pagos, int $alumno): RedirectResponse
    {
        // Un expediente con cobros no se borra, se suspende: si desapareciera
        // dejaría pagos huérfanos en tkd_pagos. Lo confirmamos antes de pedir
        // el borrado porque la llave foránea sólo existe cuando ambos módulos
        // comparten base de datos.
        try {
            $historial = $pagos->estadoCuenta($alumno)['historial'] ?? [];
        } catch (ModuloException $e) {
            return back()->with('error', 'No pudimos revisar el historial de pagos: '.$e->getMessage());
        }

        if ($historial !== []) {
            return back()->with(
                'error',
                'No se puede borrar el alumno porque tiene pagos registrados. Suspéndelo en lugar de borrarlo.',
            );
        }

        try {
            $mensaje = $alumnos->eliminar($alumno);
        } catch (ModuloException $e) {
            return back()->with('error', $e->getMessage());
        }

        // La cuenta del portal se queda sin expediente: la desvinculamos.
        User::where('alumno_id', $alumno)->update(['alumno_id' => null]);

        return redirect()->route('admin.alumnos')->with('status', $mensaje);
    }
}
