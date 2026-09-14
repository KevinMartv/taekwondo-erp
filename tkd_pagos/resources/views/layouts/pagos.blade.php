<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Pagar') · TKD Pagos</title>
    <link rel="stylesheet" href="{{ asset('css/pagos.css') }}">
</head>
<body>
    <header class="pay-header">
        <div class="pay-header__inner">
            <span class="pay-logo">TKD Pagos</span>
            <span class="pay-secure">Pago seguro</span>
        </div>
    </header>
    <main class="pay-main">
        @if (session('error'))
            <div class="pay-alert">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="pay-alert">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
