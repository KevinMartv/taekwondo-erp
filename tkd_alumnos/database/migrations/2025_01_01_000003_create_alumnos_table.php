<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->date('fecha_nacimiento');
            $table->string('telefono_contacto', 20);
            $table->foreignId('nivel_id')->constrained('niveles')->onDelete('cascade');
            $table->date('fecha_ingreso');
            $table->boolean('activo')->default(true); // Baja lógica sin borrar registros
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};