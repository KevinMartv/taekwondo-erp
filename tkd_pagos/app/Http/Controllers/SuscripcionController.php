<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Renovación de la suscripción mensual iniciada desde el panel del alumno
 * (tkd_portal). El portal firma la intención con PORTAL_SECRET y este módulo
 * la valida antes de mostrar la pasarela simulada.
 */
class SuscripcionController extends Controller
{
    public function create(Request $request): View
    {
        $intencion = $this->validarIntencion($request->query());

        return view('suscripcion.create', ['intencion' => $intencion]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'numero' => preg_replace('/\D+/', '', (string) $request->input('numero')),
        ]);

        $validated = $request->validate([
            'alumno_id' => ['required', 'integer'],
            'alumno' => ['required', 'string', 'max:150'],
            'periodo' => ['required', 'date'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'token' => ['required', 'string'],
            'return_url' => ['required', 'url'],
            'metodo_pago' => ['required', 'in:transferencia,efectivo'],
            'titular' => ['required_if:metodo_pago,transferencia', 'nullable', 'string', 'max:120'],
            'numero' => ['required_if:metodo_pago,transferencia', 'nullable', 'regex:/^[0-9]{13,19}$/'],
            'vencimiento' => ['required_if:metodo_pago,transferencia', 'nullable', 'regex:/^(0[1-9]|1[0-2])\/[0-9]{2}$/'],
            'cvv' => ['required_if:metodo_pago,transferencia', 'nullable', 'regex:/^[0-9]{3,4}$/'],
        ]);

        $intencion = $this->validarIntencion($validated);

        $yaCubierto = Pago::where('alumno_id', $intencion['alumno_id'])
            ->where('periodo_cubierto', $intencion['periodo'])
            ->where('estado', 'pagado')
            ->exists();

        if ($yaCubierto) {
            return redirect()->away($this->urlRetorno($intencion['return_url'], 'duplicada'));
        }

        $enLinea = $validated['metodo_pago'] === 'transferencia';

        Pago::create([
            'alumno_id' => $intencion['alumno_id'],
            'monto' => $intencion['monto'],
            'metodo_pago' => $validated['metodo_pago'],
            // La pasarela es simulada: generamos el folio con el que el
            // administrador puede rastrear el movimiento.
            'numero_rastreo' => $enLinea ? 'TKD-'.Str::upper(Str::random(10)) : null,
            'ciclo_pago' => (string) config('services.suscripcion.ciclo', 'mes'),
            'periodo_cubierto' => $intencion['periodo'],
            // El efectivo se cobra en recepción, así que queda pendiente de
            // confirmar por el administrador.
            'estado' => $enLinea ? 'pagado' : 'pendiente',
            'fecha_pago' => $enLinea ? Carbon::today()->toDateString() : null,
        ]);

        return redirect()->away($this->urlRetorno(
            $intencion['return_url'],
            $enLinea ? 'ok' : 'pendiente',
        ));
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array{alumno_id: int, alumno: string, periodo: string, monto: string, token: string, return_url: string}
     */
    private function validarIntencion(array $datos): array
    {
        $alumnoId = (int) ($datos['alumno_id'] ?? 0);
        $alumno = (string) ($datos['alumno'] ?? '');
        $periodo = (string) ($datos['periodo'] ?? '');
        $monto = number_format((float) ($datos['monto'] ?? 0), 2, '.', '');
        $token = (string) ($datos['token'] ?? '');
        $returnUrl = (string) ($datos['return_url'] ?? '');

        if ($alumnoId < 1 || $alumno === '' || $periodo === '' || $token === '' || $returnUrl === '') {
            abort(422, 'Faltan datos de la suscripción.');
        }

        $esperado = hash_hmac(
            'sha256',
            implode('|', ['suscripcion', $alumnoId, $alumno, $periodo, $monto, $returnUrl]),
            (string) config('services.portal.secret'),
        );

        if (! hash_equals($esperado, $token)) {
            abort(403, 'Token de suscripción inválido.');
        }

        if (! $this->retornoPermitido($returnUrl)) {
            abort(403, 'URL de retorno no permitida.');
        }

        return [
            'alumno_id' => $alumnoId,
            'alumno' => $alumno,
            'periodo' => Carbon::parse($periodo)->toDateString(),
            'monto' => $monto,
            'token' => $token,
            'return_url' => $returnUrl,
        ];
    }

    private function retornoPermitido(string $returnUrl): bool
    {
        return collect([
            config('services.portal.url'),
            config('services.portal.public_url'),
        ])
            ->filter()
            ->map(fn ($url) => rtrim((string) $url, '/'))
            ->unique()
            ->contains(fn ($origen) => $origen !== '' && str_starts_with($returnUrl, $origen));
    }

    private function urlRetorno(string $returnUrl, string $resultado): string
    {
        $separador = str_contains($returnUrl, '?') ? '&' : '?';

        return $returnUrl.$separador.'suscripcion='.$resultado;
    }
}
