<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'alumno_id',
        'monto',
        'metodo_pago',
        'numero_rastreo',
        'ciclo_pago',
        'periodo_cubierto',
        'estado',
        'fecha_pago',
    ];
}