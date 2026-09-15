<?php

namespace App\Http\Controllers;

use App\Http\Middleware\IdentificarAlumno;
use App\Models\Venta;
use App\Services\RegistrarVenta;
use App\Support\Carrito;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function store(Request $request, Carrito $carrito, RegistrarVenta $registrarVenta): RedirectResponse
    {
        $lineas = $carrito->lineas();

        if ($lineas->isEmpty()) {
            return redirect()->route('carrito.index')->with('error', 'Tu carrito está vacío.');
        }

        $venta = $registrarVenta->handle([
            'alumno_id' => $request->session()->get(IdentificarAlumno::SESSION_KEY.'.id'),
            'metodo_pago' => 'pendiente',
            'estado' => 'pendiente_pago',
            'items' => $lineas->map(fn ($linea) => [
                'producto_id' => $linea->producto->id,
                'cantidad' => $linea->cantidad,
            ])->all(),
        ]);

        $carrito->vaciar();

        return redirect()->away($venta->urlModuloPago());
    }

    public function show(Venta $venta, Carrito $carrito): View
    {
        $venta->load('detalles.producto');

        return view('compra.show', [
            'venta' => $venta,
            'carritoCount' => $carrito->cantidadTotal(),
        ]);
    }
}
