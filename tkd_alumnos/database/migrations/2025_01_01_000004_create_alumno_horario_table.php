<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_horario', function (Blueprint $table) {
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->primary(['alumno_id', 'horario_id']); // Llave primaria compuesta
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_horario');
    }
};