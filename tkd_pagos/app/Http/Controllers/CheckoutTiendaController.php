<?php

namespace App\Http\Controllers;

use App\Services\ConfirmarPagoPos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class CheckoutTiendaController extends Controller
{
    public function create(Request $request): View
    {
        $pago = $this->validarIntencion($request->query());

        return view('pagos.create', ['pago' => $pago]);
    }

    public function store(Request $request, ConfirmarPagoPos $confirmarPagoPos): RedirectResponse
    {
        $validated = $request->validate([
            'venta_id' => ['required', 'integer'],
            'referencia' => ['required', 'string', 'max:50'],
            'total' => ['required', 'numeric', 'min:0.01'],
            'token' => ['required', 'string'],
            'return_url' => ['required', 'url'],
            'metodo_pago' => ['required', 'in:tarjeta,transferencia'],
            'titular' => ['required_if:metodo_pago,tarjeta', 'nullable', 'string', 'max:120'],
            'numero' => ['required_if:metodo_pago,tarjeta', 'nullable', 'regex:/^[0-9]{13,19}$/'],
            'vencimiento' => ['required_if:metodo_pago,tarjeta', 'nullable', 'regex:/^(0[1-9]|1[0-2])\/[0-9]{2}$/'],
            'cvv' => ['required_if:metodo_pago,tarjeta', 'nullable', 'regex:/^[0-9]{3,4}$/'],
        ]);

        $pago = $this->validarIntencion($validated);

        try {
            $confirmarPagoPos->handle(
                (int) $pago['venta_id'],
                $pago['token'],
                $validated['metodo_pago'],
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput($request->except(['numero', 'cvv']))
                ->with('error', 'No se pudo confirmar el pago con el módulo POS. Intenta de nuevo.');
        }

        return redirect()->away($pago['return_url']);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array{venta_id: int, referencia: string, total: string, token: string, return_url: string}
     */
    private function validarIntencion(array $datos): array
    {
        $ventaId = (int) ($datos['venta_id'] ?? 0);
        $referencia = (string) ($datos['referencia'] ?? '');
        $total = number_format((float) ($datos['total'] ?? 0), 2, '.', '');
        $token = (string) ($datos['token'] ?? '');
        $returnUrl = (string) ($datos['return_url'] ?? '');

        if ($ventaId < 1 || $referencia === '' || $token === '' || $returnUrl === '') {
            abort(422, 'Faltan datos de la compra.');
        }

        $esperado = hash_hmac(
            'sha256',
            $ventaId.'|'.$total,
            (string) config('services.pos.secret'),
        );

        if (! hash_equals($esperado, $token)) {
            abort(403, 'Token de pago inválido.');
        }

        $origenesPermitidos = collect([
            config('services.pos.url'),
            config('services.pos.public_url'),
        ])->filter()->map(fn ($url) => rtrim((string) $url, '/'))->unique()->all();

        $retornoPermitido = collect($origenesPermitidos)
            ->contains(fn ($origen) => $origen !== '' && str_starts_with($returnUrl, $origen));

        if (! $retornoPermitido) {
            abort(403, 'URL de retorno no permitida.');
        }

        return [
            'venta_id' => $ventaId,
            'referencia' => $referencia,
            'total' => $total,
            'token' => $token,
            'return_url' => $returnUrl,
        ];
    }
}
