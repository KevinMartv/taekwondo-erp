<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un alumno sólo puede usar su panel si su cuenta está vinculada a un
 * expediente del módulo de alumnos. Si el expediente no se creó (por ejemplo
 * porque el módulo estaba caído al registrarse), lo mandamos a completarlo.
 */
class EnsureExpedienteVinculado
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->esAlumno() && ! $user->expedienteId()) {
            return redirect()
                ->route('alumno.expediente.crear')
                ->with('aviso', 'Completa tu expediente para activar tu panel de alumno.');
        }

        return $next($request);
    }
}
