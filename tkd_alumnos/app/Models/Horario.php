<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $fillable = ['dia_semana', 'hora_inicio', 'hora_fin', 'cupo_maximo'];

    public function alumnos()
    {
        return $this->belongsToMany(Alumno::class, 'alumno_horario');
    }
}