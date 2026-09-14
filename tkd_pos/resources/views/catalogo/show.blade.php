@extends('layouts.tienda')

@section('title', $producto->nombre)

@section('content')
    <p class="ml-breadcrumb">
        <a href="{{ route('catalogo.index') }}">Inicio</a> &gt;
        <a href="{{ route('catalogo.index', ['categoria' => $producto->categoria]) }}">{{ $producto->categoria }}</a> &gt;
        {{ $producto->nombre }}
    </p>

    <section class="ml-detail">
        <div class="ml-detail__gallery">
            <img src="{{ $producto->imagenPublica() }}" alt="{{ $producto->nombre }}">
        </div>
        <div class="ml-detail__info">
            <p class="ml-card__id">ID #{{ $producto->id }} · {{ $producto->categoria }}</p>
            <h1>{{ $producto->nombre }}</h1>
            <p class="ml-detail__price">${{ number_format($producto->precio, 2) }} <small>MXN</small></p>
            <p class="ml-card__cuotas">Hasta 12 x ${{ number_format($producto->precio / 12, 2) }} sin interés</p>
            <p class="ml-card__ship">{{ $producto->stock > 0 ? 'Envío gratis · '.$producto->stock.' en inventario' : 'Agotado' }}</p>
            <p class="ml-seller">Vendido por <strong>Dojang oficial</strong></p>
            <p class="ml-detail__desc">{{ $producto->descripcion }}</p>

            <form method="post" action="{{ route('carrito.store') }}" class="ml-buybox">
                @csrf
                <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                <label>
                    Cantidad
                    <input type="number" name="cantidad" min="1" max="{{ max($producto->stock, 1) }}" value="1" {{ $producto->stock < 1 ? 'disabled' : '' }}>
                </label>
                <button class="btn-primary" type="submit" {{ $producto->stock < 1 ? 'disabled' : '' }}>Agregar al carrito</button>
                <button class="btn-secondary" type="submit" name="destino" value="checkout" {{ $producto->stock < 1 ? 'disabled' : '' }}>Comprar ahora</button>
            </form>
        </div>
    </section>

    @if ($relacionados->isNotEmpty())
        <h2 class="ml-section-title">Productos relacionados</h2>
        <section class="ml-grid ml-grid--related">
            @foreach ($relacionados as $rel)
                <article class="ml-card">
                    <a href="{{ route('catalogo.show', $rel) }}" class="ml-card__image">
                        <img src="{{ $rel->imagenPublica() }}" alt="{{ $rel->nombre }}">
                    </a>
                    <div class="ml-card__body">
                        <a href="{{ route('catalogo.show', $rel) }}" class="ml-card__title">{{ $rel->nombre }}</a>
                        <p class="ml-card__price">${{ number_format($rel->precio, 2) }}</p>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
@endsection
