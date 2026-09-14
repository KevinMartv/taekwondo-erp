@extends('layouts.tienda')

@section('title', 'Carrito')

@section('content')
    <h1 class="ml-page-title">Carrito de compras</h1>

    @if ($lineas->isEmpty())
        <div class="ml-empty-cart">
            <p>Tu carrito está vacío.</p>
            <a class="btn-primary" href="{{ route('catalogo.index') }}">Ir al catálogo</a>
        </div>
    @else
        <div class="ml-cart-layout">
            <div class="ml-cart-list">
                @foreach ($lineas as $linea)
                    <article class="ml-cart-item">
                        <img src="{{ $linea->producto->imagenPublica() }}" alt="{{ $linea->producto->nombre }}">
                        <div>
                            <p class="ml-card__id">ID #{{ $linea->producto->id }}</p>
                            <a href="{{ route('catalogo.show', $linea->producto) }}">{{ $linea->producto->nombre }}</a>
                            <p class="ml-muted">{{ $linea->producto->categoria }} · ${{ number_format($linea->producto->precio, 2) }} c/u</p>
                            <form method="post" action="{{ route('carrito.update', $linea->producto) }}" class="ml-qty-form">
                                @csrf
                                @method('PATCH')
                                <label>Cantidad
                                    <input type="number" name="cantidad" min="1" max="{{ $linea->producto->stock }}" value="{{ $linea->cantidad }}">
                                </label>
                                <button type="submit">Actualizar</button>
                            </form>
                            <form method="post" action="{{ route('carrito.destroy', $linea->producto) }}">
                                @csrf
                                @method('DELETE')
                                <button class="link-danger" type="submit">Eliminar</button>
                            </form>
                        </div>
                        <p class="ml-cart-item__subtotal">${{ number_format($linea->subtotal, 2) }}</p>
                    </article>
                @endforeach
            </div>
            <aside class="ml-summary">
                <h2>Resumen de compra</h2>
                <p><span>Productos ({{ $carritoCount }})</span> <strong>${{ number_format($total, 2) }}</strong></p>
                <p class="ml-summary__total"><span>Total</span> <strong>${{ number_format($total, 2) }} MXN</strong></p>
                <form method="post" action="{{ route('checkout.store') }}">
                    @csrf
                    <button class="btn-primary btn-block" type="submit">Continuar compra</button>
                </form>
                <p class="ml-muted">Al continuar serás enviado al módulo de pagos para cerrar la orden.</p>
            </aside>
        </div>
    @endif
@endsection
