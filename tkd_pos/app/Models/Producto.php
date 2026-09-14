<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'categoria', 'descripcion', 'imagen_url', 'precio', 'stock', 'activo'])]
class Producto extends Model
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'stock' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('activo', true)->where('stock', '>', 0);
    }

    public function imagenPublica(): string
    {
        $url = $this->imagen_url ?: '/images/productos/dobok.png';

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return asset(ltrim($url, '/'));
    }
}
