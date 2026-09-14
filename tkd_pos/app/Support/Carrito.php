<?php

namespace App\Support;

use App\Models\Producto;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class Carrito
{
    public const SESSION_KEY = 'carrito';

    /**
     * @return array<int, array{producto_id: int, cantidad: int}>
     */
    public function items(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function cantidadTotal(): int
    {
        return (int) collect($this->items())->sum('cantidad');
    }

    public function agregar(Producto $producto, int $cantidad): void
    {
        if (! $producto->activo || $producto->stock < 1) {
            throw ValidationException::withMessages([
                'cantidad' => 'Este producto no está disponible.',
            ]);
        }

        $items = $this->items();
        $actual = $items[$producto->id]['cantidad'] ?? 0;
        $nueva = $actual + $cantidad;

        if ($nueva > $producto->stock) {
            throw ValidationException::withMessages([
                'cantidad' => "Solo hay {$producto->stock} unidades disponibles.",
            ]);
        }

        $items[$producto->id] = [
            'producto_id' => $producto->id,
            'cantidad' => $nueva,
        ];

        session([self::SESSION_KEY => $items]);
    }

    public function actualizar(int $productoId, int $cantidad): void
    {
        $items = $this->items();

        if (! isset($items[$productoId])) {
            return;
        }

        if ($cantidad < 1) {
            unset($items[$productoId]);
            session([self::SESSION_KEY => $items]);

            return;
        }

        $producto = Producto::query()->findOrFail($productoId);

        if ($cantidad > $producto->stock) {
            throw ValidationException::withMessages([
                'cantidad' => "Solo hay {$producto->stock} unidades disponibles.",
            ]);
        }

        $items[$productoId]['cantidad'] = $cantidad;
        session([self::SESSION_KEY => $items]);
    }

    public function quitar(int $productoId): void
    {
        $items = $this->items();
        unset($items[$productoId]);
        session([self::SESSION_KEY => $items]);
    }

    public function vaciar(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * @return Collection<int, object>
     */
    public function lineas(): Collection
    {
        $items = $this->items();
        $productos = Producto::query()->whereIn('id', array_keys($items))->get()->keyBy('id');

        return collect($items)
            ->map(function (array $item) use ($productos) {
                $producto = $productos->get($item['producto_id']);

                if (! $producto) {
                    return null;
                }

                $cantidad = (int) $item['cantidad'];
                $subtotal = $producto->precio * $cantidad;

                return (object) [
                    'producto' => $producto,
                    'cantidad' => $cantidad,
                    'subtotal' => $subtotal,
                ];
            })
            ->filter()
            ->values();
    }

    public function total(): float
    {
        return (float) $this->lineas()->sum('subtotal');
    }
}
