<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Las cuentas de acceso las administra tkd_portal (tabla compartida
     * users), así que aquí sólo sembramos el catálogo de la tienda.
     */
    public function run(): void
    {
        $this->call(ProductoSeeder::class);
    }
}
