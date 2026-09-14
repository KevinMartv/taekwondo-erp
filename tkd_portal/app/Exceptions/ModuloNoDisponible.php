<?php

namespace App\Exceptions;

use Throwable;

class ModuloNoDisponible extends ModuloException
{
    public function __construct(
        public readonly string $modulo,
        string $detalle = '',
        ?Throwable $previous = null,
    ) {
        $mensaje = "No se pudo comunicar con el módulo de {$modulo}.";

        if ($detalle !== '') {
            $mensaje .= " {$detalle}";
        }

        parent::__construct($mensaje, 0, $previous);
    }
}
