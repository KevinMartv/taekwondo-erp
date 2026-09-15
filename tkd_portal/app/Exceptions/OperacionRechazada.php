<?php

namespace App\Exceptions;

/**
 * El módulo entendió la petición pero la rechazó por una regla de negocio
 * (por ejemplo, borrar un alumno que ya tiene pagos registrados).
 */
class OperacionRechazada extends ModuloException
{
}
