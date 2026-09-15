<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Horario;
use App\Models\Nivel;
use Database\Seeders\HorarioSeeder;
use Database\Seeders\NivelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([NivelSeeder::class, HorarioSeeder::class]);
    }

    public function test_registra_un_alumno_sin_horarios(): void
    {
        $respuesta = $this->postJson('/api/alumnos', $this->datosAlumno())
            ->assertCreated()
            ->assertJsonPath('alumno.nombre', 'Ana')
            ->assertJsonPath('alumno.horarios', []);

        $this->assertDatabaseHas('alumnos', [
            'id' => $respuesta->json('alumno.id'),
            'activo' => true,
        ]);
    }

    public function test_registra_un_alumno_con_horarios(): void
    {
        $horarios = Horario::query()->take(2)->pluck('id')->all();

        $this->postJson('/api/alumnos', $this->datosAlumno(['horarios' => $horarios]))
            ->assertCreated()
            ->assertJsonCount(2, 'alumno.horarios');
    }

    public function test_rechaza_un_nivel_inexistente(): void
    {
        $this->postJson('/api/alumnos', $this->datosAlumno(['nivel_id' => 999]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nivel_id');
    }

    public function test_actualiza_los_datos_y_sincroniza_horarios(): void
    {
        $alumno = $this->crearAlumno();
        $horarios = Horario::query()->take(3)->pluck('id')->all();

        $alumno->horarios()->attach($horarios);

        $this->putJson("/api/alumnos/{$alumno->id}", [
            'telefono_contacto' => '5599887766',
            'horarios' => [$horarios[0]],
        ])
            ->assertOk()
            ->assertJsonPath('alumno.telefono_contacto', '5599887766')
            ->assertJsonCount(1, 'alumno.horarios');

        $this->assertSame([$horarios[0]], $alumno->horarios()->pluck('horarios.id')->all());
    }

    public function test_suspende_y_activa_un_alumno(): void
    {
        $alumno = $this->crearAlumno();

        $this->patchJson("/api/alumnos/{$alumno->id}/estado", ['activo' => false])
            ->assertOk()
            ->assertJsonPath('activo', false);

        $this->assertFalse((bool) $alumno->fresh()->activo);

        $this->patchJson("/api/alumnos/{$alumno->id}/estado", ['activo' => true])
            ->assertOk()
            ->assertJsonPath('activo', true);

        $this->assertTrue((bool) $alumno->fresh()->activo);
    }

    public function test_el_cambio_de_estado_exige_el_campo_activo(): void
    {
        $alumno = $this->crearAlumno();

        $this->patchJson("/api/alumnos/{$alumno->id}/estado", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('activo');
    }

    public function test_borra_un_alumno_y_libera_sus_horarios(): void
    {
        $alumno = $this->crearAlumno();
        $alumno->horarios()->attach(Horario::query()->take(2)->pluck('id')->all());

        $this->deleteJson("/api/alumnos/{$alumno->id}")->assertOk();

        $this->assertDatabaseMissing('alumnos', ['id' => $alumno->id]);
        $this->assertDatabaseMissing('alumno_horario', ['alumno_id' => $alumno->id]);
    }

    public function test_devuelve_404_al_borrar_un_expediente_inexistente(): void
    {
        $this->deleteJson('/api/alumnos/999')->assertNotFound();
    }

    public function test_expone_los_catalogos_de_niveles_y_horarios(): void
    {
        $this->getJson('/api/niveles')->assertOk()->assertJsonCount(Nivel::count());
        $this->getJson('/api/horarios')->assertOk()->assertJsonCount(Horario::count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function datosAlumno(array $overrides = []): array
    {
        return array_merge([
            'nombre' => 'Ana',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Diaz',
            'fecha_nacimiento' => '2010-05-04',
            'telefono_contacto' => '5512345678',
            'nivel_id' => Nivel::value('id'),
            'fecha_ingreso' => '2026-09-01',
        ], $overrides);
    }

    private function crearAlumno(): Alumno
    {
        return Alumno::create($this->datosAlumno());
    }
}
