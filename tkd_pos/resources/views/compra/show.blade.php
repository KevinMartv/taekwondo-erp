@extends('layouts.tienda')

@section('title', 'Compra '.$venta->referencia)

@section('content')
    <section class="ml-order">
        @if ($venta->estaPagada())
            <h1>¡Pago confirmado!</h1>
            <p class="ml-card__ship">Tu compra {{ $venta->referencia }} ya está cerrada.</p>
        @else
            <h1>Pago pendiente</h1>
            <p>Si ya pagaste, recarga esta página. Si no, vuelve al módulo de pagos para cerrar la compra.</p>
            <a class="btn-secondary" href="{{ $venta->urlModuloPago() }}">Ir a pagar</a>
        @endif

        <p class="ml-detail__price">${{ number_format($venta->total, 2) }} MXN</p>
        <p class="ml-muted">Estado: {{ $venta->estado }} · Método: {{ $venta->metodo_pago }}</p>

        <ul class="ml-order__items">
            @foreach ($venta->detalles as $detalle)
                <li>
                    <span>#{{ $detalle->producto_id }} {{ $detalle->producto->nombre ?? 'Producto' }} × {{ $detalle->cantidad }}</span>
                    <strong>${{ number_format($detalle->precio_unitario * $detalle->cantidad, 2) }}</strong>
                </li>
            @endforeach
        </ul>

        <a class="btn-primary" href="{{ route('catalogo.index') }}">Seguir comprando</a>
    </section>
@endsection
