<?php

namespace App\Http\Controllers\Alumno;

use App\Exceptions\ModuloException;
use App\Http\Controllers\Controller;
use App\Services\ModuloAlumnos;
use App\Services\ModuloPagos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AsistenciaController extends Controller
{
    public function index(Request $request, ModuloAlumnos $alumnos): View
    {
        $alumnoId = (int) $request->user()->expedienteId();

        $asistencias = [];
        $horarios = [];
        $avisos = [];

        try {
            $asistencias = $alumnos->asistencias($alumnoId);
            $horarios = $alumnos->ver($alumnoId)['horarios'] ?? [];
        } catch (ModuloException $e) {
            $avisos[] = $e->getMessage();
        }

        [$proximas, $pasadas] = collect($asistencias)
            ->partition(fn ($a) => Carbon::parse($a['fecha'])->gte(Carbon::today()));

        return view('alumno.asistencias', [
            'proximas' => $proximas->values()->all(),
            'pasadas' => $pasadas->sortByDesc('fecha')->take(10)->values()->all(),
            'horarios' => $horarios,
            'avisos' => $avisos,
        ]);
    }

    public function store(Request $request, ModuloAlumnos $alumnos, ModuloPagos $pagos): RedirectResponse
    {
        $alumnoId = (int) $request->user()->expedienteId();

        $validated = $request->validate([
            'fecha' => ['required', 'date', 'after_or_equal:today'],
            'horario_id' => ['nullable', 'integer'],
        ]);

        // Regla de negocio: no se puede reservar una clase sin tener la
        // suscripción vigente. Se consulta al módulo de Pagos, que es el
        // único dueño de ese dato.
        $estadoCuenta = $pagos->estadoCuentaSeguro($alumnoId);
        if ($estadoCuenta['vigente'] !== true) {
            return back()->withInput()->with(
                'error',
                'Necesitas estar al corriente con tu suscripción para reservar una clase. Renueva tu mensualidad primero.'
            );
        }

        try {
            $alumnos->reservarAsistencia($alumnoId, $validated);
        } catch (ModuloException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('alumno.asistencias')
            ->with('status', 'Reservaste tu clase del '.Carbon::parse($validated['fecha'])->format('d/m/Y').'.');
    }

    public function destroy(Request $request, ModuloAlumnos $alumnos, int $asistencia): RedirectResponse
    {
        $alumnoId = (int) $request->user()->expedienteId();

        try {
            $alumnos->cancelarAsistencia($alumnoId, $asistencia);
        } catch (ModuloException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('alumno.asistencias')
            ->with('status', 'Cancelaste la fecha seleccionada.');
    }
}
