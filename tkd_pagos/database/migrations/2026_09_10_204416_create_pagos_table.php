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
    Schema::create('pagos', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('alumno_id'); // FK se agrega después, cuando exista alumnos
        $table->decimal('monto', 8, 2);
        $table->enum('metodo_pago', ['efectivo', 'transferencia']);
        $table->string('numero_rastreo')->nullable(); // solo si metodo_pago es transferencia
        $table->enum('ciclo_pago', ['mes', 'quincena']);
        $table->date('periodo_cubierto'); // qué mes/quincena está cubriendo este pago
        $table->enum('estado', ['pagado', 'pendiente', 'atrasado'])->default('pendiente'); // provisional
        $table->date('fecha_pago')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
