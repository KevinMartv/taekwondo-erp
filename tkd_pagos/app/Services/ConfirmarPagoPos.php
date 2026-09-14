<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ConfirmarPagoPos
{
    public function handle(int $ventaId, string $token, string $metodoPago): array
    {
        $response = Http::baseUrl(rtrim((string) config('services.pos.url'), '/'))
            ->acceptJson()
            ->timeout(15)
            ->post("/api/ventas/{$ventaId}/pagar", [
                'metodo_pago' => $metodoPago,
                'token' => $token,
            ]);

        if ($response->failed()) {
            throw new RuntimeException('El módulo POS rechazó la confirmación de pago.');
        }

        return $response->json() ?? [];
    }
}
