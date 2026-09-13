<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'alumno_id' => ['nullable', 'integer'],
            'metodo_pago' => ['required', 'string', 'max:50'],
            'fecha' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $venta = DB::transaction(function () use ($validated) {
            $venta = Venta::query()->create([
                'alumno_id' => $validated['alumno_id'] ?? null,
                'fecha' => $validated['fecha'] ?? now(),
                'total' => 0,
                'metodo_pago' => $validated['metodo_pago'],
            ]);

            $total = 0;

            foreach ($validated['items'] as $item) {
                $producto = Producto::query()
                    ->whereKey($item['producto_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $producto->activo) {
                    throw ValidationException::withMessages([
                        'items' => "El producto {$producto->nombre} no está activo.",
                    ]);
                }

                if ($producto->stock < $item['cantidad']) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$producto->nombre}.",
                    ]);
                }

                $precioUnitario = $producto->precio;
                $cantidad = $item['cantidad'];
                $total += $precioUnitario * $cantidad;

                $venta->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                ]);

                $producto->decrement('stock', $cantidad);
            }

            $venta->update(['total' => $total]);

            return $venta->load('detalles');
        });

        return response()->json($venta, 201);
    }
}
