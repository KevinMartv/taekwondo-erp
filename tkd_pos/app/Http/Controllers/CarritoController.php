<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Support\Carrito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarritoController extends Controller
{
    public function index(Carrito $carrito): View
    {
        return view('carrito.index', [
            'lineas' => $carrito->lineas(),
            'total' => $carrito->total(),
            'carritoCount' => $carrito->cantidadTotal(),
        ]);
    }

    public function store(Request $request, Carrito $carrito): RedirectResponse
    {
        $validated = $request->validate([
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $producto = Producto::query()->findOrFail($validated['producto_id']);
        $carrito->agregar($producto, $validated['cantidad']);

        $destino = $request->input('destino') === 'checkout'
            ? route('carrito.index')
            : route('catalogo.show', $producto);

        return redirect($destino)->with('status', "{$producto->nombre} se agregó al carrito.");
    }

    public function update(Request $request, Producto $producto, Carrito $carrito): RedirectResponse
    {
        $validated = $request->validate([
            'cantidad' => ['required', 'integer', 'min:0'],
        ]);

        $carrito->actualizar($producto->id, $validated['cantidad']);

        return redirect()->route('carrito.index')->with('status', 'Carrito actualizado.');
    }

    public function destroy(Producto $producto, Carrito $carrito): RedirectResponse
    {
        $carrito->quitar($producto->id);

        return redirect()->route('carrito.index')->with('status', 'Producto eliminado del carrito.');
    }
}
