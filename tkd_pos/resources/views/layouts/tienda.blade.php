<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Tienda TKD') · Dojang Market</title>
    <link rel="stylesheet" href="{{ asset('css/tienda.css') }}">
</head>
<body>
    <header class="ml-header">
        <div class="ml-header__inner">
            <a class="ml-logo" href="{{ route('catalogo.index') }}">
                <span class="ml-logo__mark">TKD</span>
                <span class="ml-logo__text">Dojang Market</span>
            </a>
            <form class="ml-search" action="{{ route('catalogo.index') }}" method="get">
                @if (!empty($categoria))
                    <input type="hidden" name="categoria" value="{{ $categoria }}">
                @endif
                <input type="search" name="q" value="{{ $busqueda ?? request('q') }}" placeholder="Buscar productos, marcas y más…">
                <button type="submit" aria-label="Buscar">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="7"></circle>
                        <path d="M20 20l-3.5-3.5"></path>
                    </svg>
                </button>
            </form>
            <a class="ml-cart" href="{{ route('carrito.index') }}">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path d="M6 6h15l-1.5 9h-12z"></path>
                    <path d="M6 6L5 3H2"></path>
                    <circle cx="9" cy="20" r="1.4"></circle>
                    <circle cx="18" cy="20" r="1.4"></circle>
                </svg>
                @if (($carritoCount ?? 0) > 0)
                    <span class="ml-cart__badge">{{ $carritoCount }}</span>
                @endif
                <span class="ml-cart__label">Carrito</span>
            </a>
        </div>
        @if (session('tkd_alumno'))
            <div class="ml-identity">
                <span>Comprando como <strong>{{ session('tkd_alumno.nombre') }}</strong> · alumno #{{ session('tkd_alumno.id') }}</span>
                <a href="{{ rtrim((string) config('services.portal.url'), '/') }}/mi-cuenta">Volver al portal</a>
            </div>
        @endif
        <nav class="ml-nav">
            <div class="ml-nav__inner">
                <a href="{{ route('catalogo.index') }}">Todos</a>
                <a href="{{ route('catalogo.index', ['categoria' => 'Uniforme']) }}">Uniforme</a>
                <a href="{{ route('catalogo.index', ['categoria' => 'Protección']) }}">Protección</a>
                <a href="{{ route('catalogo.index', ['categoria' => 'Entrenamiento']) }}">Entrenamiento</a>
                <a href="{{ route('catalogo.index', ['categoria' => 'Cinturones']) }}">Cinturones</a>
                <a href="{{ route('catalogo.index', ['categoria' => 'Calzado']) }}">Calzado</a>
                <a href="{{ route('catalogo.index', ['categoria' => 'Accesorios']) }}">Accesorios</a>
            </div>
        </nav>
    </header>

    <main class="ml-main">
        @if (session('status'))
            <div class="ml-alert ml-alert--ok">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="ml-alert ml-alert--error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="ml-alert ml-alert--error">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="ml-footer">
        <p>Dojang Market · Módulo POS de la escuela de taekwondo · Imágenes de autoría original del catálogo</p>
    </footer>
</body>
</html>
