<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistrarVenta
{
    /**
     * @param  array{alumno_id?: int|null, metodo_pago: string, fecha?: string, estado?: string, items: array<int, array{producto_id: int, cantidad: int}>}  $datos
     */
    public function handle(array $datos): Venta
    {
        return DB::transaction(function () use ($datos) {
            $venta = Venta::query()->create([
                'alumno_id' => $datos['alumno_id'] ?? null,
                'fecha' => $datos['fecha'] ?? now(),
                'total' => 0,
                'metodo_pago' => $datos['metodo_pago'],
                'estado' => $datos['estado'] ?? 'pendiente_pago',
            ]);

            $total = 0;

            foreach ($datos['items'] as $item) {
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

            $venta->update([
                'total' => $total,
                'referencia' => 'TKD-'.str_pad((string) $venta->id, 6, '0', STR_PAD_LEFT),
            ]);

            return $venta->load('detalles.producto');
        });
    }
}
