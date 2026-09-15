<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Panel de administrador: ve el alumnado con el indicador de pago que aporta
 * tkd_pagos y puede editar, suspender, activar, borrar y cobrar.
 */
class PanelAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_el_listado_cruza_el_expediente_con_el_indicador_de_pago(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos' => Http::response([
                $this->alumno(1, 'Ana', true),
                $this->alumno(2, 'Luis', false),
            ]),
            'pagos.test/api/alumnos/1/estado-cuenta' => Http::response(['estado_cuenta' => 'al_dia', 'vigente' => true]),
            'pagos.test/api/alumnos/2/estado-cuenta' => Http::response(['estado_cuenta' => 'vencido', 'vigente' => false]),
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/alumnos')
            ->assertOk()
            ->assertSee('Ana')
            ->assertSee('Luis')
            ->assertViewHas('resumen', ['total' => 2, 'activos' => 1, 'al_dia' => 1]);
    }

    public function test_el_listado_filtra_por_estado_y_por_adeudo(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos' => Http::response([
                $this->alumno(1, 'Ana', true),
                $this->alumno(2, 'Luis', false),
            ]),
            'pagos.test/api/alumnos/1/estado-cuenta' => Http::response(['estado_cuenta' => 'al_dia']),
            'pagos.test/api/alumnos/2/estado-cuenta' => Http::response(['estado_cuenta' => 'vencido']),
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/alumnos?estado=suspendidos')
            ->assertOk()
            ->assertViewHas('alumnado', fn ($alumnado) => $alumnado->pluck('id')->all() === [2]);

        $this->actingAs($this->admin)
            ->get('/admin/alumnos?estado=con_adeudo')
            ->assertOk()
            ->assertViewHas('alumnado', fn ($alumnado) => $alumnado->pluck('id')->all() === [2]);

        $this->actingAs($this->admin)
            ->get('/admin/alumnos?q=ana')
            ->assertOk()
            ->assertViewHas('alumnado', fn ($alumnado) => $alumnado->pluck('id')->all() === [1]);
    }

    public function test_un_modulo_de_pagos_caido_no_tumba_el_listado(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos' => Http::response([$this->alumno(1, 'Ana', true)]),
            'pagos.test/*' => Http::response([], 500),
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/alumnos')
            ->assertOk()
            ->assertViewHas('alumnado', fn ($alumnado) => $alumnado[0]['suscripcion']['estado_cuenta'] === 'desconocido');
    }

    public function test_el_modulo_de_alumnos_caido_se_reporta_como_aviso(): void
    {
        Http::fake(['alumnos.test/*' => Http::response([], 503)]);

        $this->actingAs($this->admin)
            ->get('/admin/alumnos')
            ->assertOk()
            ->assertViewHas('avisos', fn (array $avisos) => count($avisos) === 1);
    }

    public function test_la_pantalla_de_edicion_reune_catalogos_y_cuenta_vinculada(): void
    {
        $cuenta = User::factory()->alumno(1)->create(['email' => 'ana@example.com']);

        Http::fake([
            'alumnos.test/api/alumnos/1' => Http::response($this->alumno(1, 'Ana', true)),
            'alumnos.test/api/niveles' => Http::response([['id' => 1, 'nombre' => 'Cinturón Blanco']]),
            'alumnos.test/api/horarios' => Http::response([['id' => 1, 'dia_semana' => 'lunes', 'hora_inicio' => '17:00:00', 'hora_fin' => '18:30:00']]),
            'pagos.test/api/alumnos/1/estado-cuenta' => Http::response(['estado_cuenta' => 'al_dia']),
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/alumnos/1/editar')
            ->assertOk()
            ->assertSee('ana@example.com')
            ->assertViewHas('cuenta', fn (?User $vinculada) => $vinculada?->is($cuenta));
    }

    public function test_el_administrador_actualiza_el_expediente_incluido_el_nivel(): void
    {
        Http::fake(['alumnos.test/api/alumnos/1' => Http::response(['alumno' => $this->alumno(1, 'Ana', true)])]);

        $this->actingAs($this->admin)
            ->put('/admin/alumnos/1', [
                'nombre' => 'Ana',
                'apellido_paterno' => 'Lopez',
                'apellido_materno' => 'Diaz',
                'fecha_nacimiento' => '2010-05-04',
                'telefono_contacto' => '5512345678',
                'nivel_id' => 4,
                'fecha_ingreso' => '2026-09-01',
                'horarios' => [1, 2],
            ])
            ->assertRedirect(route('admin.alumnos'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request) => $request['nivel_id'] === 4 && $request['horarios'] === [1, 2]);
    }

    public function test_el_administrador_suspende_y_activa(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos/1/estado' => Http::response(['message' => 'El alumno ha sido suspendido correctamente', 'activo' => false]),
        ]);

        $this->actingAs($this->admin)
            ->from('/admin/alumnos')
            ->patch('/admin/alumnos/1/estado', ['activo' => 0])
            ->assertRedirect('/admin/alumnos')
            ->assertSessionHas('status', 'El alumno ha sido suspendido correctamente');

        Http::assertSent(fn ($request) => $request->method() === 'PATCH' && $request['activo'] === false);
    }

    public function test_el_borrado_desvincula_la_cuenta_del_portal(): void
    {
        $cuenta = User::factory()->alumno(1)->create();

        Http::fake([
            'pagos.test/api/alumnos/1/estado-cuenta' => Http::response(['historial' => []]),
            'alumnos.test/api/alumnos/1' => Http::response(['message' => 'Alumno eliminado correctamente']),
        ]);

        $this->actingAs($this->admin)
            ->delete('/admin/alumnos/1')
            ->assertRedirect(route('admin.alumnos'))
            ->assertSessionHas('status');

        $this->assertNull($cuenta->fresh()->expedienteId());
    }

    public function test_el_borrado_rechazado_por_tener_pagos_se_explica_al_administrador(): void
    {
        $cuenta = User::factory()->alumno(1)->create();

        Http::fake([
            'pagos.test/api/alumnos/1/estado-cuenta' => Http::response([
                'historial' => [['id' => 9, 'monto' => 500]],
            ]),
        ]);

        $this->actingAs($this->admin)
            ->from('/admin/alumnos')
            ->delete('/admin/alumnos/1')
            ->assertRedirect('/admin/alumnos')
            ->assertSessionHas('error', 'No se puede borrar el alumno porque tiene pagos registrados. Suspéndelo en lugar de borrarlo.');

        $this->assertSame(1, $cuenta->fresh()->expedienteId());
        Http::assertNotSent(fn ($request) => $request->method() === 'DELETE');
    }

    public function test_el_administrador_registra_un_pago_en_efectivo(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos' => Http::response([$this->alumno(1, 'Ana', true)]),
            'pagos.test/api/pagos' => Http::response(['message' => 'Pago registrado'], 201),
            'pagos.test/api/alumnos/1/estado-cuenta' => Http::response(['estado_cuenta' => 'al_dia']),
        ]);

        $this->actingAs($this->admin)
            ->post('/admin/pagos', [
                'alumno_id' => 1,
                'monto' => 500,
                'metodo_pago' => 'efectivo',
                'ciclo_pago' => 'mes',
                'periodo_cubierto' => '2026-10-01',
                'fecha_pago' => '2026-10-01',
            ])
            ->assertRedirect(route('admin.pagos', ['alumno_id' => 1]))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request) => $request->url() === 'http://pagos.test/api/pagos'
            && $request['metodo_pago'] === 'efectivo');
    }

    public function test_una_transferencia_exige_numero_de_rastreo(): void
    {
        $this->actingAs($this->admin)
            ->post('/admin/pagos', [
                'alumno_id' => 1,
                'monto' => 500,
                'metodo_pago' => 'transferencia',
                'ciclo_pago' => 'mes',
                'periodo_cubierto' => '2026-10-01',
                'fecha_pago' => '2026-10-01',
            ])
            ->assertSessionHasErrors('numero_rastreo');

        Http::assertNothingSent();
    }

    public function test_el_administrador_confirma_una_renovacion_pendiente(): void
    {
        Http::fake(['pagos.test/api/pagos/9/confirmar' => Http::response(['message' => 'Pago confirmado'])]);

        $this->actingAs($this->admin)
            ->from('/admin/pagos')
            ->patch('/admin/pagos/9/confirmar')
            ->assertRedirect('/admin/pagos')
            ->assertSessionHas('status');

        Http::assertSent(fn ($request) => $request->method() === 'PATCH'
            && $request->url() === 'http://pagos.test/api/pagos/9/confirmar');
    }

    public function test_la_consulta_de_estado_de_cuenta_se_muestra_en_pagos(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos' => Http::response([$this->alumno(1, 'Ana', true)]),
            'pagos.test/api/alumnos/1/estado-cuenta' => Http::response([
                'estado_cuenta' => 'al_dia',
                'proximo_vencimiento' => '2026-11-01',
                'pagos_pendientes' => [],
            ]),
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/pagos?alumno_id=1')
            ->assertOk()
            ->assertViewHas('consulta.estado_cuenta', 'al_dia')
            ->assertViewHas('consultaId', 1);
    }

    /**
     * @return array<string, mixed>
     */
    private function alumno(int $id, string $nombre, bool $activo): array
    {
        return [
            'id' => $id,
            'nombre' => $nombre,
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Diaz',
            'fecha_nacimiento' => '2010-05-04',
            'telefono_contacto' => '5512345678',
            'nivel_id' => 1,
            'fecha_ingreso' => '2026-09-01',
            'activo' => $activo,
            'nivel' => ['id' => 1, 'nombre' => 'Cinturón Blanco'],
            'horarios' => [],
        ];
    }
}
