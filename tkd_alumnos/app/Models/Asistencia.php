<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asistencia extends Model
{
    use HasFactory;

    protected $table = 'asistencias';

    protected $fillable = [
        'alumno_id',
        'horario_id',
        'fecha',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }
}
