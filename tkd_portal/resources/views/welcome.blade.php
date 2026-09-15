<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dojang ERP · Escuela de Taekwondo</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @include('layouts.assets')
</head>
<body class="font-sans antialiased bg-gray-900 text-gray-100">
 
    <header class="border-b border-white/10">
        <div class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="grid place-items-center w-11 h-11 rounded-full bg-red-600 font-bold">태</span>
                <div>
                    <p class="font-semibold leading-tight">Dojang ERP</p>
                    <p class="text-xs text-gray-400">Escuela de Taekwondo</p>
                </div>
            </div>
 
            <nav class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-5 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-sm font-semibold">
                        Ir a mi panel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-5 py-2 rounded-lg border border-white/20 hover:border-white/40 text-sm font-semibold">
                        Iniciar sesión
                    </a>
                    <a href="{{ route('register') }}" class="px-5 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-sm font-semibold">
                        Crear cuenta
                    </a>
                @endauth
            </nav>
        </div>
    </header>
 
    <main>
        <section class="max-w-6xl mx-auto px-6 py-20 grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <p class="inline-block px-3 py-1 rounded-full bg-red-600/15 text-red-400 text-xs font-semibold tracking-wide uppercase">
                    Sistema integral de la escuela
                </p>
                <h1 class="mt-5 text-4xl sm:text-5xl font-bold leading-tight">
                    Tu dojang, tus clases y tus pagos <span class="text-red-500">en un solo lugar</span>
                </h1>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="px-6 py-3 rounded-lg bg-red-600 hover:bg-red-500 font-semibold">
                        Registrarme como alumno
                    </a>
                    <a href="{{ route('login') }}" class="px-6 py-3 rounded-lg border border-white/20 hover:border-white/40 font-semibold">
                        Ya tengo cuenta
                    </a>
                </div>
            </div>
 
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                    <p class="text-2xl">🥋</p>
                    <h3 class="mt-3 font-semibold">Perfil del alumno</h3>
                    <p class="mt-1 text-sm text-gray-400">Edita tus datos de contacto y consulta tu grado actual.</p>
                </div>
                <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                    <p class="text-2xl">📅</p>
                    <h3 class="mt-3 font-semibold">Horarios y asistencia</h3>
                    <p class="mt-1 text-sm text-gray-400">Elige los días que entrenas y reserva tus fechas de clase.</p>
                </div>
                <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                    <p class="text-2xl">⏳</p>
                    <h3 class="mt-3 font-semibold">Vigencia mensual</h3>
                    <p class="mt-1 text-sm text-gray-400">Un contador te avisa cuántos días faltan para tu próximo pago.</p>
                </div>
                <div class="p-5 rounded-2xl bg-white/5 border border-white/10">
                    <p class="text-2xl">🛒</p>
                    <h3 class="mt-3 font-semibold">Tienda del dojang</h3>
                    <p class="mt-1 text-sm text-gray-400">Compra uniformes y protecciones; el cobro pasa al módulo de pagos.</p>
                </div>
            </div>
        </section>
 
        <section class="border-t border-white/10 bg-black/20">
            <div class="max-w-6xl mx-auto px-6 py-14 grid md:grid-cols-2 gap-8">
                <div class="p-6 rounded-2xl border border-white/10">
                    <h2 class="text-lg font-semibold">Nivel alumno</h2>
                    <ul class="mt-4 space-y-2 text-sm text-gray-400">
                        <li>· Edita únicamente sus propios datos</li>
                        <li>· Selecciona horarios y fechas de asistencia</li>
                        <li>· Renueva su suscripción desde su cuenta</li>
                        <li>· Compra en la tienda y paga en línea</li>
                        <li>· Ve el contador de vencimiento de su mensualidad</li>
                    </ul>
                </div>
                <div class="p-6 rounded-2xl border border-white/10">
                    <h2 class="text-lg font-semibold">Nivel administrador</h2>
                    <ul class="mt-4 space-y-2 text-sm text-gray-400">
                        <li>· Ve el expediente completo de cada alumno</li>
                        <li>· Edita, suspende, activa y borra alumnos</li>
                        <li>· Indicador de pago: quién está al día y quién no</li>
                        <li>· Registra y confirma mensualidades</li>
                        <li>· Consulta el inventario del punto de venta</li>
                    </ul>
                </div>
            </div>
        </section>
    </main>
 
    <footer class="border-t border-white/10">
        <div class="max-w-6xl mx-auto px-6 py-8 text-sm text-gray-500 flex flex-wrap justify-between gap-3">
            <p>Dojang ERP · Portal central del sistema</p>
            <p>Módulos: alumnos · pagos · punto de venta</p>
        </div>
    </footer>
</body>
</html>