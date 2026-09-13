<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Nivel;

class NivelSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            ['nombre' => 'Cinturón Blanco', 'orden' => 1],
            ['nombre' => 'Cinturón Amarillo', 'orden' => 2],
            ['nombre' => 'Cinturón Verde', 'orden' => 3],
            ['nombre' => 'Cinturón Azul', 'orden' => 4],
            ['nombre' => 'Cinturón Rojo', 'orden' => 5],
            ['nombre' => 'Cinturón Negro', 'orden' => 6],
        ];

        foreach ($niveles as $nivel) {
            Nivel::firstOrCreate(['nombre' => $nivel['nombre']], $nivel);
        }
    }
}