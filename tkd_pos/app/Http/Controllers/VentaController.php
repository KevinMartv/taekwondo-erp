<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Services\RegistrarVenta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function store(Request $request, RegistrarVenta $registrarVenta): JsonResponse
    {
        $validated = $request->validate([
            'alumno_id' => ['nullable', 'integer'],
            'metodo_pago' => ['required', 'string', 'max:50'],
            'fecha' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'integer', 'min:1'],
        ]);

        $metodo = $validated['metodo_pago'];
        $validated['estado'] = $metodo === 'pendiente' ? 'pendiente_pago' : 'pagada';

        $venta = $registrarVenta->handle($validated);

        return response()->json($venta, 201);
    }

    public function confirmarPago(Request $request, Venta $venta): JsonResponse
    {
        $validated = $request->validate([
            'metodo_pago' => ['required', 'string', 'max:50'],
            'token' => ['required', 'string'],
        ]);

        $esperado = $venta->tokenPago();

        if (! hash_equals($esperado, $validated['token'])) {
            abort(403, 'Token de pago inválido.');
        }

        if (! $venta->estaPagada()) {
            $venta->update([
                'metodo_pago' => $validated['metodo_pago'],
                'estado' => 'pagada',
            ]);
        }

        return response()->json($venta->fresh('detalles'));
    }
}
