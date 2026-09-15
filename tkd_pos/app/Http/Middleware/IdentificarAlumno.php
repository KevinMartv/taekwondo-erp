<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * El portal manda al alumno a la tienda con su identidad firmada
 * (?alumno_id=&alumno=&token=). La guardamos en sesión para que la venta
 * quede a su nombre sin que el POS necesite su propio login.
 */
class IdentificarAlumno
{
    public const SESSION_KEY = 'tkd_alumno';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->boolean('salir_alumno')) {
            $request->session()->forget(self::SESSION_KEY);
        }

        $alumnoId = (int) $request->query('alumno_id', 0);
        $alumno = (string) $request->query('alumno', '');
        $token = (string) $request->query('token', '');

        if ($alumnoId > 0 && $alumno !== '' && $token !== '') {
            $esperado = hash_hmac(
                'sha256',
                implode('|', ['tienda', $alumnoId, $alumno]),
                (string) config('services.portal.secret'),
            );

            if (hash_equals($esperado, $token)) {
                $request->session()->put(self::SESSION_KEY, [
                    'id' => $alumnoId,
                    'nombre' => $alumno,
                ]);
            }
        }

        return $next($request);
    }
}
