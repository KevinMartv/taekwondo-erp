<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Panel del alumno. Los módulos vecinos se simulan con Http::fake, así que lo
 * que se verifica es el contrato: qué pide el portal, con qué datos y a dónde
 * manda al alumno después.
 */
class PanelAlumnoTest extends TestCase
{
    use RefreshDatabase;

    private const ALUMNO_ID = 7;

    private User $alumno;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $this->alumno = User::factory()->alumno(self::ALUMNO_ID)->create(['name' => 'Ana Lopez']);
    }

    public function test_la_cuenta_muestra_expediente_vigencia_y_proximas_clases(): void
    {
        Carbon::setTestNow('2026-10-10');

        Http::fake([
            'alumnos.test/api/alumnos/7' => Http::response($this->expediente()),
            'alumnos.test/api/alumnos/7/asistencias' => Http::response([
                ['id' => 1, 'fecha' => '2026-09-01', 'estado' => 'asistio', 'horario' => null],
                ['id' => 2, 'fecha' => '2026-10-12', 'estado' => 'reservada', 'horario' => null],
            ]),
            'pagos.test/api/alumnos/7/estado-cuenta' => Http::response([
                'estado_cuenta' => 'al_dia',
                'vigente' => true,
                'proximo_vencimiento' => '2026-11-01',
                'dias_restantes' => 22,
                'periodo_sugerido' => '2026-11-01',
                'monto_mensualidad' => 500,
            ]),
        ]);

        $respuesta = $this->actingAs($this->alumno)->get('/mi-cuenta')->assertOk();

        $respuesta->assertSee('Ana');
        $respuesta->assertSee('22');
        $respuesta->assertViewHas('suscripcion.estado_cuenta', 'al_dia');
        // Sólo las fechas de hoy en adelante son "próximas clases".
        $respuesta->assertViewHas('asistencias', fn (array $asistencias) => count($asistencias) === 1
            && $asistencias[0]['fecha'] === '2026-10-12');

        Carbon::setTestNow();
    }

    public function test_la_cuenta_avisa_cuando_un_modulo_no_responde(): void
    {
        Http::fake([
            'alumnos.test/*' => Http::response([], 500),
            'pagos.test/*' => Http::response([], 500),
        ]);

        $this->actingAs($this->alumno)
            ->get('/mi-cuenta')
            ->assertOk()
            ->assertViewHas('avisos', fn (array $avisos) => count($avisos) === 2)
            ->assertViewHas('suscripcion', null);
    }

    public function test_el_enlace_a_la_tienda_va_firmado_con_la_identidad_del_alumno(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos/7' => Http::response($this->expediente()),
            'alumnos.test/api/alumnos/7/asistencias' => Http::response([]),
            'pagos.test/api/alumnos/7/estado-cuenta' => Http::response(['estado_cuenta' => 'al_dia']),
        ]);

        $url = $this->actingAs($this->alumno)->get('/mi-cuenta')->viewData('urlTienda');

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $this->assertStringStartsWith('http://pos.test/', $url);
        $this->assertSame('7', $query['alumno_id']);
        $this->assertSame('Ana Lopez', $query['alumno']);
        $this->assertSame(
            hash_hmac('sha256', 'tienda|7|Ana Lopez', 'test-portal-secret'),
            $query['token'],
        );
    }

    public function test_la_tienda_redirige_al_pos_con_la_venta_atribuida(): void
    {
        Http::fake(['alumnos.test/api/alumnos/7' => Http::response($this->expediente())]);

        $respuesta = $this->actingAs($this->alumno)->get('/mi-cuenta/tienda');

        $respuesta->assertRedirectContains('http://pos.test/');
        $respuesta->assertRedirectContains('alumno_id=7');
    }

    public function test_el_alumno_solo_edita_su_propio_expediente(): void
    {
        Http::fake(['alumnos.test/api/alumnos/7' => Http::response(['alumno' => $this->expediente()])]);

        $this->actingAs($this->alumno)
            ->put('/mi-cuenta/perfil', [
                'nombre' => 'Ana Maria',
                'apellido_paterno' => 'Lopez',
                'apellido_materno' => 'Diaz',
                'fecha_nacimiento' => '2010-05-04',
                'telefono_contacto' => '5599887766',
                // Intento de escalar privilegios: el nivel y el estado los fija el admin.
                'nivel_id' => 6,
                'activo' => 1,
            ])
            ->assertRedirect(route('alumno.perfil'))
            ->assertSessionHas('status');

        Http::assertSent(function ($request) {
            return $request->url() === 'http://alumnos.test/api/alumnos/7'
                && $request->method() === 'PUT'
                && $request['telefono_contacto'] === '5599887766'
                && ! array_key_exists('nivel_id', $request->data())
                && ! array_key_exists('activo', $request->data());
        });

        $this->assertSame('Ana Maria Lopez', $this->alumno->fresh()->name);
    }

    public function test_el_perfil_propaga_los_errores_de_validacion_del_modulo(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos/7' => Http::response([
                'message' => 'Datos inválidos.',
                'errors' => ['telefono_contacto' => ['El teléfono es muy largo.']],
            ], 422),
        ]);

        $this->actingAs($this->alumno)
            ->put('/mi-cuenta/perfil', [
                'nombre' => 'Ana',
                'apellido_paterno' => 'Lopez',
                'fecha_nacimiento' => '2010-05-04',
                'telefono_contacto' => '5599887766',
            ])
            ->assertSessionHasErrors('telefono_contacto');
    }

    public function test_el_alumno_guarda_sus_horarios(): void
    {
        Http::fake(['alumnos.test/api/alumnos/7' => Http::response(['alumno' => $this->expediente()])]);

        $this->actingAs($this->alumno)
            ->put('/mi-cuenta/horarios', ['horarios' => [1, 3]])
            ->assertRedirect(route('alumno.horarios'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request) => $request->method() === 'PUT'
            && $request['horarios'] === [1, 3]);
    }

    public function test_al_vaciar_la_seleccion_se_envia_una_lista_vacia(): void
    {
        Http::fake(['alumnos.test/api/alumnos/7' => Http::response(['alumno' => $this->expediente()])]);

        $this->actingAs($this->alumno)->put('/mi-cuenta/horarios', [])->assertRedirect(route('alumno.horarios'));

        Http::assertSent(fn ($request) => $request['horarios'] === []);
    }

    public function test_el_alumno_reserva_una_fecha_de_asistencia(): void
    {
        Carbon::setTestNow('2026-10-10');

        Http::fake([
            'alumnos.test/api/alumnos/7/asistencias' => Http::response(['message' => 'ok'], 201),
        ]);

        $this->actingAs($this->alumno)
            ->post('/mi-cuenta/asistencias', ['fecha' => '2026-10-15', 'horario_id' => 3])
            ->assertRedirect(route('alumno.asistencias'))
            ->assertSessionHas('status', 'Reservaste tu clase del 15/10/2026.');

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request['fecha'] === '2026-10-15'
            && $request['horario_id'] === 3);

        Carbon::setTestNow();
    }

    public function test_no_se_reservan_fechas_pasadas(): void
    {
        Carbon::setTestNow('2026-10-10');

        $this->actingAs($this->alumno)
            ->post('/mi-cuenta/asistencias', ['fecha' => '2026-10-01'])
            ->assertSessionHasErrors('fecha');

        Http::assertNothingSent();

        Carbon::setTestNow();
    }

    public function test_el_alumno_cancela_una_fecha_reservada(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos/7/asistencias/5' => Http::response(['message' => 'Fecha de asistencia cancelada']),
        ]);

        $this->actingAs($this->alumno)
            ->delete('/mi-cuenta/asistencias/5')
            ->assertRedirect(route('alumno.asistencias'))
            ->assertSessionHas('status');

        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && $request->url() === 'http://alumnos.test/api/alumnos/7/asistencias/5');
    }

    public function test_la_renovacion_manda_a_la_pasarela_firmada_del_modulo_de_pagos(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos/7' => Http::response($this->expediente()),
            'pagos.test/api/alumnos/7/estado-cuenta' => Http::response([
                'estado_cuenta' => 'vencido',
                'periodo_sugerido' => '2026-11-01',
                'monto_mensualidad' => 500,
            ]),
        ]);

        $respuesta = $this->actingAs($this->alumno)->post('/mi-cuenta/suscripcion/renovar');

        $destino = $respuesta->headers->get('Location');
        parse_str((string) parse_url($destino, PHP_URL_QUERY), $query);

        $this->assertStringStartsWith('http://pagos.test/suscripcion?', (string) $destino);
        $this->assertSame('2026-11-01', $query['periodo']);
        $this->assertSame('500.00', $query['monto']);
        $this->assertSame(route('alumno.mi_cuenta'), $query['return_url']);
        $this->assertSame(
            hash_hmac(
                'sha256',
                implode('|', ['suscripcion', 7, 'Ana Lopez', '2026-11-01', '500.00', route('alumno.mi_cuenta')]),
                'test-portal-secret',
            ),
            $query['token'],
        );
    }

    public function test_un_alumno_suspendido_no_puede_renovar(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos/7' => Http::response($this->expediente(['activo' => false])),
            'pagos.test/api/alumnos/7/estado-cuenta' => Http::response(['estado_cuenta' => 'vencido']),
        ]);

        $this->actingAs($this->alumno)
            ->post('/mi-cuenta/suscripcion/renovar')
            ->assertSessionHas('error');
    }

    public function test_la_cuenta_confirma_el_regreso_desde_la_pasarela(): void
    {
        Http::fake([
            'alumnos.test/api/alumnos/7' => Http::response($this->expediente()),
            'alumnos.test/api/alumnos/7/asistencias' => Http::response([]),
            'pagos.test/api/alumnos/7/estado-cuenta' => Http::response(['estado_cuenta' => 'al_dia']),
        ]);

        $this->actingAs($this->alumno)
            ->get('/mi-cuenta?suscripcion=pendiente')
            ->assertOk()
            ->assertViewHas('avisos', fn (array $avisos) => str_contains($avisos[0], 'efectivo'));
    }

    public function test_la_cuenta_sin_expediente_lo_crea_y_queda_vinculada(): void
    {
        $usuario = User::factory()->alumno(null)->create();

        Http::fake([
            'alumnos.test/api/niveles' => Http::response([['id' => 1, 'nombre' => 'Cinturón Blanco']]),
            'alumnos.test/api/alumnos' => Http::response(['alumno' => $this->expediente(['id' => 42])], 201),
        ]);

        $this->actingAs($usuario)
            ->post('/mi-cuenta/expediente', [
                'nombre' => 'Ana',
                'apellido_paterno' => 'Lopez',
                'fecha_nacimiento' => '2010-05-04',
                'telefono_contacto' => '5512345678',
                'nivel_id' => 1,
            ])
            ->assertRedirect(route('alumno.mi_cuenta'));

        $this->assertSame(42, $usuario->fresh()->expedienteId());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function expediente(array $overrides = []): array
    {
        return array_merge([
            'id' => self::ALUMNO_ID,
            'nombre' => 'Ana',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Diaz',
            'fecha_nacimiento' => '2010-05-04',
            'telefono_contacto' => '5512345678',
            'nivel_id' => 1,
            'fecha_ingreso' => '2026-09-01',
            'activo' => true,
            'nivel' => ['id' => 1, 'nombre' => 'Cinturón Blanco'],
            'horarios' => [
                ['id' => 1, 'dia_semana' => 'lunes', 'hora_inicio' => '17:00:00', 'hora_fin' => '18:30:00'],
            ],
        ], $overrides);
    }
}
