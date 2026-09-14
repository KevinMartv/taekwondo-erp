<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('horario_id')->nullable()->constrained('horarios')->nullOnDelete();
            $table->date('fecha');
            $table->enum('estado', ['reservada', 'asistio', 'falta'])->default('reservada');
            $table->timestamps();

            // Un alumno sólo puede reservar una vez la misma fecha
            $table->unique(['alumno_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
