<?php

namespace Tests\Feature;

use App\Models\Pago;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SuscripcionTest extends TestCase
{
    use RefreshDatabase;

    public function test_muestra_la_pasarela_con_una_intencion_firmada(): void
    {
        $this->get('/suscripcion?'.http_build_query($this->intencion()))
            ->assertOk()
            ->assertSee('Renovar mensualidad')
            ->assertSee('Ana Lopez');
    }

    public function test_rechaza_una_intencion_sin_firma_valida(): void
    {
        $this->get('/suscripcion?'.http_build_query($this->intencion(['token' => 'token-falso'])))
            ->assertForbidden();
    }

    public function test_rechaza_una_url_de_retorno_ajena_al_portal(): void
    {
        $intencion = $this->intencion(['return_url' => 'http://sitio-malicioso.test/cobrar']);
        $intencion['token'] = $this->firma($intencion);

        $this->get('/suscripcion?'.http_build_query($intencion))
            ->assertForbidden();
    }

    public function test_el_pago_en_linea_queda_liquidado_y_extiende_la_vigencia(): void
    {
        $intencion = $this->intencion();

        $this->post('/suscripcion', [
            ...$intencion,
            'metodo_pago' => 'transferencia',
            'titular' => 'Ana Lopez',
            'numero' => '4242 4242 4242 4242',
            'vencimiento' => '12/30',
            'cvv' => '123',
        ])->assertRedirect('http://portal.test/mi-cuenta?suscripcion=ok');

        $pago = Pago::firstOrFail();

        $this->assertSame(9, (int) $pago->alumno_id);
        $this->assertSame('pagado', $pago->estado);
        $this->assertSame('mes', $pago->ciclo_pago);
        $this->assertNotNull($pago->numero_rastreo);
        $this->assertEquals(500, $pago->monto);
    }

    public function test_el_efectivo_queda_pendiente_de_confirmar(): void
    {
        $this->post('/suscripcion', [
            ...$this->intencion(),
            'metodo_pago' => 'efectivo',
        ])->assertRedirect('http://portal.test/mi-cuenta?suscripcion=pendiente');

        $pago = Pago::firstOrFail();

        $this->assertSame('pendiente', $pago->estado);
        $this->assertNull($pago->fecha_pago);
        $this->assertNull($pago->numero_rastreo);
    }

    public function test_no_cobra_dos_veces_el_mismo_periodo(): void
    {
        Pago::create([
            'alumno_id' => 9,
            'monto' => 500,
            'metodo_pago' => 'transferencia',
            'numero_rastreo' => 'TKD-EXISTENTE',
            'ciclo_pago' => 'mes',
            'periodo_cubierto' => '2026-10-01',
            'estado' => 'pagado',
            'fecha_pago' => '2026-10-01',
        ]);

        $this->post('/suscripcion', [
            ...$this->intencion(),
            'metodo_pago' => 'efectivo',
        ])->assertRedirect('http://portal.test/mi-cuenta?suscripcion=duplicada');

        $this->assertSame(1, Pago::count());
    }

    public function test_el_estado_de_cuenta_calcula_los_dias_restantes(): void
    {
        Carbon::setTestNow('2026-10-10');

        Pago::create([
            'alumno_id' => 9,
            'monto' => 500,
            'metodo_pago' => 'transferencia',
            'ciclo_pago' => 'mes',
            'periodo_cubierto' => '2026-10-01',
            'estado' => 'pagado',
            'fecha_pago' => '2026-10-01',
        ]);

        $this->getJson('/api/alumnos/9/estado-cuenta')
            ->assertOk()
            ->assertJson([
                'estado_cuenta' => 'al_dia',
                'vigente' => true,
                'proximo_vencimiento' => '2026-11-01',
                'dias_restantes' => 22,
                'periodo_sugerido' => '2026-11-01',
            ]);

        Carbon::setTestNow();
    }

    public function test_un_pago_pendiente_no_cuenta_como_vigencia(): void
    {
        Carbon::setTestNow('2026-10-10');

        Pago::create([
            'alumno_id' => 9,
            'monto' => 500,
            'metodo_pago' => 'efectivo',
            'ciclo_pago' => 'mes',
            'periodo_cubierto' => '2026-10-01',
            'estado' => 'pendiente',
        ]);

        $respuesta = $this->getJson('/api/alumnos/9/estado-cuenta')
            ->assertOk()
            ->assertJson([
                'estado_cuenta' => 'sin_historial',
                'vigente' => false,
            ]);

        $this->assertCount(1, $respuesta->json('pagos_pendientes'));

        Carbon::setTestNow();
    }

    public function test_el_administrador_confirma_un_pago_pendiente(): void
    {
        $pago = Pago::create([
            'alumno_id' => 9,
            'monto' => 500,
            'metodo_pago' => 'efectivo',
            'ciclo_pago' => 'mes',
            'periodo_cubierto' => '2026-10-01',
            'estado' => 'pendiente',
        ]);

        $this->patchJson("/api/pagos/{$pago->id}/confirmar")->assertOk();

        $pago->refresh();

        $this->assertSame('pagado', $pago->estado);
        $this->assertNotNull($pago->fecha_pago);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function intencion(array $overrides = []): array
    {
        $intencion = [
            'alumno_id' => 9,
            'alumno' => 'Ana Lopez',
            'periodo' => '2026-10-01',
            'monto' => '500.00',
            'return_url' => 'http://portal.test/mi-cuenta',
        ];

        $intencion['token'] = $this->firma($intencion);

        return array_merge($intencion, $overrides);
    }

    /**
     * @param  array<string, mixed>  $intencion
     */
    private function firma(array $intencion): string
    {
        return hash_hmac('sha256', implode('|', [
            'suscripcion',
            $intencion['alumno_id'],
            $intencion['alumno'],
            $intencion['periodo'],
            $intencion['monto'],
            $intencion['return_url'],
        ]), 'test-portal-secret');
    }
}
