<?php

namespace Tests\Feature;

use App\Http\Middleware\IdentificarAlumno;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * La tienda no tiene login propio: confía en la identidad firmada que le manda
 * el portal para atribuir la venta al alumno correcto.
 */
class IdentidadAlumnoTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_identidad_firmada_queda_en_sesion(): void
    {
        $this->get(route('catalogo.index', $this->identidad(7, 'Ana Lopez')))
            ->assertOk()
            ->assertSee('Comprando como')
            ->assertSee('Ana Lopez')
            ->assertSessionHas(IdentificarAlumno::SESSION_KEY, ['id' => 7, 'nombre' => 'Ana Lopez']);
    }

    public function test_una_firma_invalida_se_ignora(): void
    {
        $this->get(route('catalogo.index', [
            'alumno_id' => 7,
            'alumno' => 'Ana Lopez',
            'token' => 'firma-falsa',
        ]))
            ->assertOk()
            ->assertDontSee('Comprando como')
            ->assertSessionMissing(IdentificarAlumno::SESSION_KEY);
    }

    public function test_no_se_puede_suplantar_a_otro_alumno_reusando_el_token(): void
    {
        $identidad = $this->identidad(7, 'Ana Lopez');
        $identidad['alumno_id'] = 8;

        $this->get(route('catalogo.index', $identidad))
            ->assertOk()
            ->assertSessionMissing(IdentificarAlumno::SESSION_KEY);
    }

    public function test_la_venta_queda_a_nombre_del_alumno_identificado(): void
    {
        $producto = Producto::factory()->create(['precio' => 100, 'stock' => 5]);

        $this->get(route('catalogo.index', $this->identidad(7, 'Ana Lopez')));

        $this->post(route('carrito.store'), ['producto_id' => $producto->id, 'cantidad' => 1]);
        $this->post(route('checkout.store'));

        $this->assertSame(7, (int) Venta::firstOrFail()->alumno_id);
    }

    public function test_una_compra_de_mostrador_no_lleva_alumno(): void
    {
        $producto = Producto::factory()->create(['precio' => 100, 'stock' => 5]);

        $this->post(route('carrito.store'), ['producto_id' => $producto->id, 'cantidad' => 1]);
        $this->post(route('checkout.store'));

        $this->assertNull(Venta::firstOrFail()->alumno_id);
    }

    public function test_el_alumno_puede_cerrar_su_identidad(): void
    {
        $this->get(route('catalogo.index', $this->identidad(7, 'Ana Lopez')))
            ->assertSessionHas(IdentificarAlumno::SESSION_KEY);

        $this->get(route('catalogo.index', ['salir_alumno' => 1]))
            ->assertOk()
            ->assertSessionMissing(IdentificarAlumno::SESSION_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    private function identidad(int $alumnoId, string $alumno): array
    {
        return [
            'alumno_id' => $alumnoId,
            'alumno' => $alumno,
            'token' => hash_hmac(
                'sha256',
                implode('|', ['tienda', $alumnoId, $alumno]),
                'test-portal-secret',
            ),
        ];
    }
}
