<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Reparto de privilegios por nivel de acceso: el visitante sólo ve la
 * bienvenida, el alumno sólo su expediente y el administrador todo el alumnado.
 */
class NivelesAccesoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ninguna prueba de acceso debe salir a los otros módulos de verdad.
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response([])]);
    }

    public function test_el_visitante_ve_el_portal_de_bienvenida_con_acceso_y_registro(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(route('login'))
            ->assertSee(route('register'))
            ->assertSee('Registrarme como alumno')
            ->assertSee('Ya tengo cuenta');
    }

    public function test_el_login_ofrece_entrar_o_crear_cuenta(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Iniciar sesión')
            ->assertSee('Crear cuenta de alumno');
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function rutasPrivadas(): array
    {
        return [
            'panel del alumno' => ['get', '/mi-cuenta'],
            'perfil del alumno' => ['get', '/mi-cuenta/perfil'],
            'horarios del alumno' => ['get', '/mi-cuenta/horarios'],
            'asistencias del alumno' => ['get', '/mi-cuenta/asistencias'],
            'alumnado del administrador' => ['get', '/admin/alumnos'],
            'pagos del administrador' => ['get', '/admin/pagos'],
        ];
    }

    #[DataProvider('rutasPrivadas')]
    public function test_el_visitante_no_entra_a_ningun_panel(string $metodo, string $ruta): void
    {
        $this->{$metodo}($ruta)->assertRedirect(route('login'));
    }

    public function test_el_alumno_aterriza_en_su_cuenta_desde_el_dashboard(): void
    {
        $this->actingAs(User::factory()->alumno(7)->create())
            ->get('/dashboard')
            ->assertRedirect(route('alumno.mi_cuenta'));
    }

    public function test_el_administrador_aterriza_en_el_alumnado_desde_el_dashboard(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard')
            ->assertRedirect(route('admin.alumnos'));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: array<string, mixed>}>
     */
    public static function rutasDeAdministrador(): array
    {
        return [
            'listado' => ['get', '/admin/alumnos', []],
            'editar' => ['get', '/admin/alumnos/1/editar', []],
            'actualizar' => ['put', '/admin/alumnos/1', []],
            'suspender' => ['patch', '/admin/alumnos/1/estado', ['activo' => 0]],
            'borrar' => ['delete', '/admin/alumnos/1', []],
            'pagos' => ['get', '/admin/pagos', []],
            'registrar pago' => ['post', '/admin/pagos', []],
            'confirmar pago' => ['patch', '/admin/pagos/1/confirmar', []],
            'inventario' => ['get', '/admin/pos-inventario', []],
        ];
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    #[DataProvider('rutasDeAdministrador')]
    public function test_el_alumno_no_toca_el_panel_de_administrador(string $metodo, string $ruta, array $datos): void
    {
        $this->actingAs(User::factory()->alumno(7)->create())
            ->{$metodo}($ruta, $datos)
            ->assertForbidden();
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: array<string, mixed>}>
     */
    public static function rutasDeAlumno(): array
    {
        return [
            'mi cuenta' => ['get', '/mi-cuenta', []],
            'perfil' => ['get', '/mi-cuenta/perfil', []],
            'actualizar perfil' => ['put', '/mi-cuenta/perfil', []],
            'horarios' => ['get', '/mi-cuenta/horarios', []],
            'asistencias' => ['get', '/mi-cuenta/asistencias', []],
            'reservar clase' => ['post', '/mi-cuenta/asistencias', []],
            'renovar suscripcion' => ['post', '/mi-cuenta/suscripcion/renovar', []],
            'tienda' => ['get', '/mi-cuenta/tienda', []],
        ];
    }

    /**
     * El administrador gestiona expedientes ajenos desde su propio panel: el
     * panel personal del alumno no le corresponde.
     *
     * @param  array<string, mixed>  $datos
     */
    #[DataProvider('rutasDeAlumno')]
    public function test_el_administrador_no_usa_el_panel_personal_del_alumno(string $metodo, string $ruta, array $datos): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->{$metodo}($ruta, $datos)
            ->assertForbidden();
    }

    public function test_el_alumno_sin_expediente_es_enviado_a_completarlo(): void
    {
        $this->actingAs(User::factory()->alumno(null)->create())
            ->get('/mi-cuenta')
            ->assertRedirect(route('alumno.expediente.crear'))
            ->assertSessionHas('aviso');
    }

    public function test_el_alumno_ya_vinculado_no_repite_el_alta_de_expediente(): void
    {
        $this->actingAs(User::factory()->alumno(7)->create())
            ->get('/mi-cuenta/expediente')
            ->assertRedirect(route('alumno.mi_cuenta'));
    }
}
