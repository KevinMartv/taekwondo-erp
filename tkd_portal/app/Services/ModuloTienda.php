<?php

namespace App\Services;

/**
 * Enlace firmado hacia la tienda (tkd_pos). El POS no tiene login propio:
 * confía en la identidad que firma el portal para atribuir la venta.
 */
class ModuloTienda
{
    public function url(int $alumnoId, string $alumno): string
    {
        $token = hash_hmac(
            'sha256',
            implode('|', ['tienda', $alumnoId, $alumno]),
            (string) config('services.portal.secret'),
        );

        $query = http_build_query([
            'alumno_id' => $alumnoId,
            'alumno' => $alumno,
            'token' => $token,
        ]);

        return rtrim((string) config('services.modules.pos.url'), '/')."/?{$query}";
    }
}
