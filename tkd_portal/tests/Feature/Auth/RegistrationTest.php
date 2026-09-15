<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        Http::fake(['alumnos.test/api/niveles' => Http::response($this->niveles())]);

        $this->get('/register')
            ->assertOk()
            ->assertSee('Cinturón Blanco');
    }

    public function test_la_cuenta_nueva_nace_como_alumno_y_crea_su_expediente(): void
    {
        Http::fake([
            'alumnos.test/api/niveles' => Http::response($this->niveles()),
            'alumnos.test/api/alumnos' => Http::response(['alumno' => ['id' => 31]], 201),
        ]);

        $this->post('/register', $this->datos())
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();

        $usuario = User::firstWhere('email', 'ana@example.com');

        $this->assertSame('alumno', $usuario->role);
        $this->assertSame('Ana Lopez', $usuario->name);
        $this->assertSame(31, $usuario->expedienteId());

        Http::assertSent(fn ($request) => $request->url() === 'http://alumnos.test/api/alumnos'
            && $request['nivel_id'] === 1
            && $request['fecha_ingreso'] !== null);
    }

    public function test_el_registro_pide_los_datos_del_expediente(): void
    {
        Http::fake(['alumnos.test/api/niveles' => Http::response($this->niveles())]);

        $this->post('/register', ['email' => 'ana@example.com'])
            ->assertSessionHasErrors(['nombre', 'apellido_paterno', 'fecha_nacimiento', 'telefono_contacto', 'nivel_id', 'password']);

        $this->assertGuest();
    }

    public function test_si_el_modulo_de_alumnos_esta_caido_la_cuenta_queda_sin_expediente(): void
    {
        Http::fake(['alumnos.test/*' => Http::response([], 500)]);

        $this->post('/register', $this->datos())
            ->assertRedirect(route('dashboard', absolute: false))
            ->assertSessionHas('aviso');

        $usuario = User::firstWhere('email', 'ana@example.com');

        $this->assertSame('alumno', $usuario->role);
        $this->assertNull($usuario->expedienteId());
    }

    public function test_el_expediente_rechazado_no_deja_la_cuenta_a_medias(): void
    {
        Http::fake([
            'alumnos.test/api/niveles' => Http::response($this->niveles()),
            'alumnos.test/api/alumnos' => Http::response([
                'message' => 'Datos inválidos.',
                'errors' => ['nivel_id' => ['El nivel no existe.']],
            ], 422),
        ]);

        $this->post('/register', $this->datos())
            ->assertRedirect(route('dashboard', absolute: false))
            ->assertSessionHas('aviso');

        $this->assertNull(User::firstWhere('email', 'ana@example.com')->expedienteId());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function niveles(): array
    {
        return [
            ['id' => 1, 'nombre' => 'Cinturón Blanco', 'orden' => 1],
            ['id' => 2, 'nombre' => 'Cinturón Amarillo', 'orden' => 2],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function datos(): array
    {
        return [
            'nombre' => 'Ana',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Diaz',
            'fecha_nacimiento' => '2010-05-04',
            'telefono_contacto' => '5512345678',
            'nivel_id' => 1,
            'email' => 'ana@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }
}
