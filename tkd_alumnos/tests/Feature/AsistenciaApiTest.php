<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Horario;
use App\Models\Nivel;
use Database\Seeders\HorarioSeeder;
use Database\Seeders\NivelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsistenciaApiTest extends TestCase
{
    use RefreshDatabase;

    private Alumno $alumno;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([NivelSeeder::class, HorarioSeeder::class]);

        $this->alumno = Alumno::create([
            'nombre' => 'Ana',
            'apellido_paterno' => 'Lopez',
            'apellido_materno' => 'Diaz',
            'fecha_nacimiento' => '2010-05-04',
            'telefono_contacto' => '5512345678',
            'nivel_id' => Nivel::value('id'),
            'fecha_ingreso' => '2026-09-01',
        ]);
    }

    public function test_reserva_una_fecha_de_clase(): void
    {
        $horario = Horario::query()->firstOrFail();
        $this->alumno->horarios()->attach($horario->id);

        $this->postJson("/api/alumnos/{$this->alumno->id}/asistencias", [
            'fecha' => '2026-10-05',
            'horario_id' => $horario->id,
        ])
            ->assertCreated()
            ->assertJsonPath('asistencia.estado', 'reservada')
            ->assertJsonPath('asistencia.horario.dia_semana', $horario->dia_semana);

        $this->assertDatabaseHas('asistencias', [
            'alumno_id' => $this->alumno->id,
            'fecha' => '2026-10-05',
            'estado' => 'reservada',
        ]);
    }

    public function test_reserva_sin_horario_asignado(): void
    {
        $this->postJson("/api/alumnos/{$this->alumno->id}/asistencias", [
            'fecha' => '2026-10-06',
        ])
            ->assertCreated()
            ->assertJsonPath('asistencia.horario_id', null);
    }

    public function test_no_permite_dos_reservas_en_la_misma_fecha(): void
    {
        $this->postJson("/api/alumnos/{$this->alumno->id}/asistencias", ['fecha' => '2026-10-07'])
            ->assertCreated();

        $this->postJson("/api/alumnos/{$this->alumno->id}/asistencias", ['fecha' => '2026-10-07'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('fecha');
    }

    public function test_rechaza_un_horario_que_no_pertenece_al_alumno(): void
    {
        $ajeno = Horario::query()->latest('id')->firstOrFail();

        $this->postJson("/api/alumnos/{$this->alumno->id}/asistencias", [
            'fecha' => '2026-10-08',
            'horario_id' => $ajeno->id,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'El horario seleccionado no está asignado a este alumno.');

        $this->assertDatabaseCount('asistencias', 0);
    }

    public function test_lista_las_asistencias_filtrando_por_rango(): void
    {
        foreach (['2026-09-20', '2026-10-01', '2026-10-15'] as $fecha) {
            $this->alumno->asistencias()->create(['fecha' => $fecha]);
        }

        $this->getJson("/api/alumnos/{$this->alumno->id}/asistencias?desde=2026-10-01&hasta=2026-10-10")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.fecha', '2026-10-01');
    }

    public function test_cancela_una_reserva(): void
    {
        $asistencia = $this->alumno->asistencias()->create(['fecha' => '2026-10-09']);

        $this->deleteJson("/api/alumnos/{$this->alumno->id}/asistencias/{$asistencia->id}")
            ->assertOk();

        $this->assertDatabaseCount('asistencias', 0);
    }

    public function test_no_cancela_la_reserva_de_otro_alumno(): void
    {
        $otro = Alumno::create([
            'nombre' => 'Luis',
            'apellido_paterno' => 'Perez',
            'fecha_nacimiento' => '2009-01-01',
            'telefono_contacto' => '5500000000',
            'nivel_id' => Nivel::value('id'),
            'fecha_ingreso' => '2026-09-01',
        ]);

        $asistencia = $otro->asistencias()->create(['fecha' => '2026-10-10']);

        $this->deleteJson("/api/alumnos/{$this->alumno->id}/asistencias/{$asistencia->id}")
            ->assertNotFound();

        $this->assertDatabaseCount('asistencias', 1);
    }

    public function test_al_borrar_el_alumno_se_borran_sus_asistencias(): void
    {
        $this->alumno->asistencias()->create(['fecha' => '2026-10-11']);

        $this->deleteJson("/api/alumnos/{$this->alumno->id}")->assertOk();

        $this->assertDatabaseCount('asistencias', 0);
    }
}
