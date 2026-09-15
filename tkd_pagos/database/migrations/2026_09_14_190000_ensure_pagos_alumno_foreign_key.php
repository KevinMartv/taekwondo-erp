<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La llave de pagos.alumno_id depende de una tabla que crea otro módulo
 * (tkd_alumnos). Si este servicio migra primero, la llave original se omite;
 * esta migración la coloca en cuanto la tabla ya existe y es idempotente,
 * así que puede volver a ejecutarse sin romper nada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('alumnos') || $this->yaTieneLlave()) {
            return;
        }

        Schema::table('pagos', function (Blueprint $table) {
            $table->foreign('alumno_id')->references('id')->on('alumnos')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        if (! $this->yaTieneLlave()) {
            return;
        }

        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['alumno_id']);
        });
    }

    private function yaTieneLlave(): bool
    {
        foreach (Schema::getForeignKeys('pagos') as $llave) {
            if (in_array('alumno_id', $llave['columns'], true)) {
                return true;
            }
        }

        return false;
    }
};
