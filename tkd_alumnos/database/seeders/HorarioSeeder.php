<?php

namespace Database\Seeders;

use App\Models\Horario;
use Illuminate\Database\Seeder;

class HorarioSeeder extends Seeder
{
    public function run(): void
    {
        $horarios = [
            ['dia_semana' => 'lunes', 'hora_inicio' => '17:00:00', 'hora_fin' => '18:30:00', 'cupo_maximo' => 20],
            ['dia_semana' => 'martes', 'hora_inicio' => '17:00:00', 'hora_fin' => '18:30:00', 'cupo_maximo' => 20],
            ['dia_semana' => 'miercoles', 'hora_inicio' => '17:00:00', 'hora_fin' => '18:30:00', 'cupo_maximo' => 20],
            ['dia_semana' => 'jueves', 'hora_inicio' => '19:00:00', 'hora_fin' => '20:30:00', 'cupo_maximo' => 15],
            ['dia_semana' => 'viernes', 'hora_inicio' => '19:00:00', 'hora_fin' => '20:30:00', 'cupo_maximo' => 15],
            ['dia_semana' => 'sabado', 'hora_inicio' => '09:00:00', 'hora_fin' => '11:00:00', 'cupo_maximo' => 30],
        ];

        foreach ($horarios as $horario) {
            Horario::firstOrCreate([
                'dia_semana' => $horario['dia_semana'],
                'hora_inicio' => $horario['hora_inicio'],
            ], $horario);
        }
    }
}
