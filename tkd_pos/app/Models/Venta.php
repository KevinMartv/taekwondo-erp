<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['alumno_id', 'fecha', 'total', 'metodo_pago', 'estado', 'referencia'])]
class Venta extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function totalFormateado(): string
    {
        return number_format((float) $this->total, 2, '.', '');
    }

    public function tokenPago(): string
    {
        return hash_hmac('sha256', $this->id.'|'.$this->totalFormateado(), (string) config('services.pagos.secret'));
    }

    public function urlModuloPago(?string $returnUrl = null): string
    {
        $pagosUrl = rtrim((string) config('services.pagos.url'), '/');
        $query = http_build_query([
            'venta_id' => $this->id,
            'referencia' => $this->referencia,
            'total' => $this->totalFormateado(),
            'token' => $this->tokenPago(),
            'return_url' => $returnUrl ?? route('compra.show', $this),
        ]);

        return "{$pagosUrl}/pagar?{$query}";
    }

    public function estaPagada(): bool
    {
        return $this->estado === 'pagada';
    }
}
