<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Support\Carrito;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index(Request $request, Carrito $carrito): View
    {
        $busqueda = trim((string) $request->query('q', ''));
        $categoria = trim((string) $request->query('categoria', ''));

        $productos = Producto::query()
            ->where('activo', true)
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($inner) use ($busqueda) {
                    $inner->where('nombre', 'like', "%{$busqueda}%")
                        ->orWhere('descripcion', 'like', "%{$busqueda}%")
                        ->orWhere('categoria', 'like', "%{$busqueda}%");
                });
            })
            ->when($categoria !== '', fn ($query) => $query->where('categoria', $categoria))
            ->orderBy('nombre')
            ->get();

        $categorias = Producto::query()
            ->where('activo', true)
            ->distinct()
            ->orderBy('categoria')
            ->pluck('categoria');

        return view('catalogo.index', [
            'productos' => $productos,
            'categorias' => $categorias,
            'busqueda' => $busqueda,
            'categoria' => $categoria,
            'carritoCount' => $carrito->cantidadTotal(),
        ]);
    }

    public function show(Producto $producto, Carrito $carrito): View
    {
        abort_unless($producto->activo, 404);

        $relacionados = Producto::query()
            ->where('activo', true)
            ->where('categoria', $producto->categoria)
            ->whereKeyNot($producto->id)
            ->limit(4)
            ->get();

        return view('catalogo.show', [
            'producto' => $producto,
            'relacionados' => $relacionados,
            'carritoCount' => $carrito->cantidadTotal(),
        ]);
    }
}
