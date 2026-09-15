<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Cualquier problema al operar contra otro módulo del ERP. Los controladores
 * la capturan y la muestran como aviso en pantalla en vez de un error 500.
 */
abstract class ModuloException extends RuntimeException
{
}
