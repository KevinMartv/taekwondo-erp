<?php

namespace App\Http\Controllers\Alumno;

use App\Http\Controllers\Controller;
use App\Services\ModuloTienda;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TiendaController extends Controller
{
    /**
     * Manda al alumno al catálogo del POS con su identidad firmada, para que
     * la compra quede registrada a su nombre y termine en el módulo de pagos.
     */
    public function __invoke(Request $request, ModuloTienda $tienda): RedirectResponse
    {
        return redirect()->away(
            $tienda->url((int) $request->user()->expedienteId(), $request->user()->name),
        );
    }
}
