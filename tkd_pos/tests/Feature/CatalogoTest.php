<?php

namespace Tests\Feature;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_catalogo_muestra_productos_activos(): void
    {
        $producto = Producto::factory()->create([
            'nombre' => 'Dobok de prueba',
            'precio' => 500,
            'stock' => 4,
        ]);

        $this->get(route('catalogo.index'))
            ->assertOk()
            ->assertSee('Dobok de prueba')
            ->assertSee('ID #'.$producto->id)
            ->assertSee('500.00');
    }

    public function test_se_puede_agregar_un_producto_al_carrito(): void
    {
        $producto = Producto::factory()->create(['stock' => 5]);

        $this->from(route('catalogo.show', $producto))
            ->post(route('carrito.store'), [
                'producto_id' => $producto->id,
                'cantidad' => 2,
            ])
            ->assertRedirect();

        $this->get(route('carrito.index'))
            ->assertOk()
            ->assertSee($producto->nombre)
            ->assertSee('2');
    }

    public function test_checkout_crea_venta_y_redirige_al_modulo_de_pagos(): void
    {
        $producto = Producto::factory()->create([
            'precio' => 100,
            'stock' => 5,
        ]);

        $this->post(route('carrito.store'), [
            'producto_id' => $producto->id,
            'cantidad' => 2,
        ]);

        $response = $this->post(route('checkout.store'));

        $venta = Venta::query()->first();
        $this->assertNotNull($venta);
        $this->assertSame('200.00', $venta->total);
        $this->assertSame('pendiente_pago', $venta->estado);
        $this->assertSame(3, $producto->fresh()->stock);

        $response->assertRedirect($venta->urlModuloPago());
    }

    public function test_confirmar_pago_marca_la_venta_como_pagada(): void
    {
        $producto = Producto::factory()->create(['precio' => 50, 'stock' => 2]);

        $this->post('/api/ventas', [
            'metodo_pago' => 'pendiente',
            'items' => [
                ['producto_id' => $producto->id, 'cantidad' => 1],
            ],
        ])->assertCreated();

        $venta = Venta::query()->first();

        $this->postJson('/api/ventas/'.$venta->id.'/pagar', [
            'metodo_pago' => 'tarjeta',
            'token' => $venta->tokenPago(),
        ])->assertOk()
            ->assertJsonPath('estado', 'pagada')
            ->assertJsonPath('metodo_pago', 'tarjeta');
    }
}
