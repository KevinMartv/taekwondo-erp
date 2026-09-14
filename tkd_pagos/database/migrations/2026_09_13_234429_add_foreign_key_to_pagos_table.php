<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    // La tabla alumnos la crea el módulo tkd_alumnos. Si este módulo se migra
    // por su cuenta (por ejemplo en las pruebas) simplemente no hay llave.
    if (! Schema::hasTable('alumnos')) {
        return;
    }

    Schema::table('pagos', function (Blueprint $table) {
        $table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('restrict');
    });
}

public function down(): void
{
    if (! Schema::hasTable('alumnos')) {
        return;
    }

    Schema::table('pagos', function (Blueprint $table) {
        $table->dropForeign(['alumno_id']);
    });
}
};
