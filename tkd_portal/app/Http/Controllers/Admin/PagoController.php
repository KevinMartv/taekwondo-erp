<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ModuloException;
use App\Http\Controllers\Controller;
use App\Services\ModuloAlumnos;
use App\Services\ModuloPagos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Control de cobros desde el portal: registra mensualidades, confirma las
 * renovaciones que quedaron pendientes y consulta el estado de cuenta.
 */
class PagoController extends Controller
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

        $consultaId = (int) $request->query('alumno_id', 0);
        $consulta = null;

        if ($consultaId > 0) {
            try {
                $consulta = $pagos->estadoCuenta($consultaId);
            } catch (ModuloException $e) {
                $avisos[] = $e->getMessage();
            }
        }

        return view('admin.pagos', [
            'alumnado' => collect($listado)->values(),
            'consultaId' => $consultaId,
            'consulta' => $consulta,
            'avisos' => $avisos,
            'mensualidad' => (float) config('services.suscripcion.monto'),
        ]);
    }

    public function store(Request $request, ModuloPagos $pagos): RedirectResponse
    {
        $validated = $request->validate([
            'alumno_id' => ['required', 'integer', 'min:1'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'metodo_pago' => ['required', 'in:efectivo,transferencia'],
            'numero_rastreo' => ['required_if:metodo_pago,transferencia', 'nullable', 'string', 'max:100'],
            'ciclo_pago' => ['required', 'in:mes,quincena'],
            'periodo_cubierto' => ['required', 'date'],
            'fecha_pago' => ['required', 'date'],
        ]);

        try {
            $pagos->registrarPago($validated);
        } catch (ModuloException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.pagos', ['alumno_id' => $validated['alumno_id']])
            ->with('status', 'Pago registrado para el alumno #'.$validated['alumno_id'].'.');
    }

    public function confirmar(Request $request, ModuloPagos $pagos, int $pago): RedirectResponse
    {
        try {
            $pagos->confirmarPago($pago);
        } catch (ModuloException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Pago #'.$pago.' confirmado el '.Carbon::today()->format('d/m/Y').'.');
    }
}
