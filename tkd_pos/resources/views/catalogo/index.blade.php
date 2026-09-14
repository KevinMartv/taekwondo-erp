@extends('layouts.tienda')

@section('title', $busqueda ? 'Resultados para '.$busqueda : 'Catálogo')

@section('content')
    <div class="ml-toolbar">
        <p class="ml-breadcrumb">Inicio &gt; Productos @if($categoria)&gt; {{ $categoria }}@endif</p>
        <p class="ml-count">{{ $productos->count() }} resultados</p>
    </div>

    <div class="ml-layout">
        <aside class="ml-filters">
            <h2>Filtrar por categoría</h2>
            <ul>
                <li>
                    <a class="{{ $categoria === '' ? 'is-active' : '' }}" href="{{ route('catalogo.index', array_filter(['q' => $busqueda])) }}">Todas</a>
                </li>
                @foreach ($categorias as $cat)
                    <li>
                        <a class="{{ $categoria === $cat ? 'is-active' : '' }}" href="{{ route('catalogo.index', array_filter(['q' => $busqueda, 'categoria' => $cat])) }}">{{ $cat }}</a>
                    </li>
                @endforeach
            </ul>
        </aside>

        <section class="ml-grid">
            @forelse ($productos as $producto)
                <article class="ml-card">
                    <a href="{{ route('catalogo.show', $producto) }}" class="ml-card__image">
                        <img src="{{ $producto->imagenPublica() }}" alt="{{ $producto->nombre }}">
                    </a>
                    <div class="ml-card__body">
                        <p class="ml-card__id">ID #{{ $producto->id }}</p>
                        <a href="{{ route('catalogo.show', $producto) }}" class="ml-card__title">{{ $producto->nombre }}</a>
                        <p class="ml-card__price">${{ number_format($producto->precio, 2) }} <span>MXN</span></p>
                        <p class="ml-card__cuotas">12 x ${{ number_format($producto->precio / 12, 2) }} sin interés</p>
                        <p class="ml-card__ship">{{ $producto->stock > 0 ? 'Envío gratis · '.$producto->stock.' disponibles' : 'Sin stock' }}</p>
                        <form method="post" action="{{ route('carrito.store') }}" class="ml-card__form">
                            @csrf
                            <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                            <label class="sr-only" for="qty-{{ $producto->id }}">Cantidad</label>
                            <input id="qty-{{ $producto->id }}" type="number" name="cantidad" min="1" max="{{ $producto->stock }}" value="1" {{ $producto->stock < 1 ? 'disabled' : '' }}>
                            <button type="submit" {{ $producto->stock < 1 ? 'disabled' : '' }}>Agregar al carrito</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="ml-empty">No encontramos productos para tu búsqueda.</p>
            @endforelse
        </section>
    </div>
@endsection
