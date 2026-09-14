<?php

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Cliente del módulo tkd_pagos: dueño de las mensualidades y por lo tanto
 * del indicador "ya pagó / no ha pagado" de cada alumno.
 */
class ModuloPagos extends ClienteModulo
{
    protected function nombre(): string
    {
        return 'pagos';
    }

    protected function baseUrl(): string
    {
        return (string) config('services.modules.pagos.api');
    }

    /**
     * @return array<string, mixed>
     */
    public function estadoCuenta(int $alumnoId): array
    {
        return $this->pedir('GET', "/alumnos/{$alumnoId}/estado-cuenta");
    }

    /**
     * Estado de cuenta tolerante a fallos: se usa en los listados, donde un
     * módulo caído no debe tumbar la página completa.
     *
     * @return array<string, mixed>
     */
    public function estadoCuentaSeguro(int $alumnoId): array
    {
        try {
            return $this->estadoCuenta($alumnoId);
        } catch (\Throwable) {
            return ['estado_cuenta' => 'desconocido', 'vigente' => null];
        }
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function registrarPago(array $datos): array
    {
        return $this->pedir('POST', '/pagos', $datos);
    }

    /**
     * @return array<string, mixed>
     */
    public function confirmarPago(int $pagoId): array
    {
        return $this->pedir('PATCH', "/pagos/{$pagoId}/confirmar");
    }

    /**
     * Construye la URL firmada de la pasarela simulada de renovación.
     * El módulo de pagos valida la firma antes de mostrar el formulario.
     */
    public function urlRenovacion(int $alumnoId, string $alumno, string $periodo, float $monto, string $returnUrl): string
    {
        $periodo = Carbon::parse($periodo)->toDateString();
        $montoFormateado = number_format($monto, 2, '.', '');

        $token = hash_hmac(
            'sha256',
            implode('|', ['suscripcion', $alumnoId, $alumno, $periodo, $montoFormateado, $returnUrl]),
            (string) config('services.portal.secret'),
        );

        $query = http_build_query([
            'alumno_id' => $alumnoId,
            'alumno' => $alumno,
            'periodo' => $periodo,
            'monto' => $montoFormateado,
            'token' => $token,
            'return_url' => $returnUrl,
        ]);

        return rtrim((string) config('services.modules.pagos.url'), '/')."/suscripcion?{$query}";
    }
}
