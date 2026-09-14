@php
    use Illuminate\Support\Carbon;

    $estado = $suscripcion['estado_cuenta'] ?? 'desconocido';
    $dias = $suscripcion['dias_restantes'] ?? null;
    $vencimiento = $suscripcion['proximo_vencimiento'] ?? null;
    $monto = (float) ($suscripcion['monto_mensualidad'] ?? config('services.suscripcion.monto'));
    $pendientes = $suscripcion['pagos_pendientes'] ?? [];

    $paleta = match ($estado) {
        'al_dia' => ($suscripcion['por_vencer'] ?? false)
            ? ['bg-amber-50', 'border-amber-200', 'text-amber-800']
            : ['bg-green-50', 'border-green-200', 'text-green-800'],
        'con_adeudo' => ['bg-red-50', 'border-red-200', 'text-red-800'],
        'sin_historial' => ['bg-gray-50', 'border-gray-200', 'text-gray-700'],
        default => ['bg-gray-50', 'border-gray-200', 'text-gray-700'],
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mi cuenta</h2>
            @if ($expediente)
                <span class="text-sm px-3 py-1 rounded-full {{ ($expediente['activo'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ ($expediente['activo'] ?? false) ? 'Alumno activo' : 'Alumno suspendido' }}
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-flash :avisos="$avisos" />

            @if ($expediente && ! ($expediente['activo'] ?? true))
                <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    Tu cuenta está suspendida por el administrador. Ponte al día con tu mensualidad
                    para volver a entrenar.
                </div>
            @endif

            <div class="grid lg:grid-cols-3 gap-6">

                <!-- Contador de vencimiento de la suscripción -->
                <div class="lg:col-span-2 p-6 rounded-2xl border {{ $paleta[0] }} {{ $paleta[1] }} {{ $paleta[2] }}">
                    <p class="text-xs font-semibold uppercase tracking-wider">Suscripción mensual</p>

                    @if ($estado === 'al_dia')
                        <p class="mt-2 text-3xl font-bold">
                            {{ $dias }} {{ abs((int) $dias) === 1 ? 'día' : 'días' }} de vigencia
                        </p>
                        <p class="mt-1 text-sm">Vence el {{ Carbon::parse($vencimiento)->format('d/m/Y') }}</p>
                        @if ($vencimiento)
                            <p class="mt-3 text-sm font-mono" data-cuenta-regresiva="{{ Carbon::parse($vencimiento)->endOfDay()->toIso8601String() }}">
                                calculando…
                            </p>
                        @endif
                    @elseif ($estado === 'con_adeudo')
                        <p class="mt-2 text-3xl font-bold">Mensualidad vencida</p>
                        <p class="mt-1 text-sm">
                            Venció el {{ Carbon::parse($vencimiento)->format('d/m/Y') }}
                            ({{ abs((int) $dias) }} {{ abs((int) $dias) === 1 ? 'día' : 'días' }} de atraso)
                        </p>
                    @elseif ($estado === 'sin_historial')
                        <p class="mt-2 text-3xl font-bold">Sin pagos registrados</p>
                        <p class="mt-1 text-sm">Activa tu suscripción para empezar a entrenar.</p>
                    @else
                        <p class="mt-2 text-3xl font-bold">Estado no disponible</p>
                        <p class="mt-1 text-sm">No pudimos consultar el módulo de pagos.</p>
                    @endif

                    <form method="POST" action="{{ route('alumno.suscripcion.renovar') }}" class="mt-5">
                        @csrf
                        <button type="submit"
                                class="px-6 py-3 rounded-lg bg-gray-900 hover:bg-gray-800 text-white text-sm font-semibold">
                            Renovar por ${{ number_format($monto, 2) }} MXN
                        </button>
                        <span class="block mt-2 text-xs opacity-80">
                            Te llevamos al módulo de pagos para completar el cobro.
                        </span>
                    </form>

                    @if (count($pendientes) > 0)
                        <div class="mt-5 pt-4 border-t border-black/10 text-sm">
                            <p class="font-semibold">Pagos por confirmar en recepción</p>
                            <ul class="mt-2 space-y-1">
                                @foreach ($pendientes as $pendiente)
                                    <li>
                                        Periodo {{ Carbon::parse($pendiente['periodo_cubierto'])->format('d/m/Y') }} ·
                                        ${{ number_format((float) $pendiente['monto'], 2) }} ·
                                        {{ $pendiente['metodo_pago'] }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Expediente -->
                <div class="p-6 rounded-2xl bg-white border border-gray-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Mi expediente</p>

                    @if ($expediente)
                        <p class="mt-2 text-lg font-semibold text-gray-800">
                            {{ $expediente['nombre'] }} {{ $expediente['apellido_paterno'] }} {{ $expediente['apellido_materno'] }}
                        </p>
                        <dl class="mt-4 space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between gap-3">
                                <dt>Expediente</dt>
                                <dd class="font-medium text-gray-800">#{{ $expediente['id'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Grado</dt>
                                <dd class="font-medium text-gray-800">{{ $expediente['nivel']['nombre'] ?? 'Sin asignar' }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Teléfono</dt>
                                <dd class="font-medium text-gray-800">{{ $expediente['telefono_contacto'] }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt>Ingreso</dt>
                                <dd class="font-medium text-gray-800">{{ Carbon::parse($expediente['fecha_ingreso'])->format('d/m/Y') }}</dd>
                            </div>
                        </dl>

                        <a href="{{ route('alumno.perfil') }}" class="mt-4 inline-block text-sm font-semibold text-red-600 hover:text-red-500">
                            Editar mis datos →
                        </a>
                    @else
                        <p class="mt-2 text-sm text-gray-500">No pudimos cargar tu expediente.</p>
                    @endif
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">

                <!-- Horarios asignados -->
                <div class="p-6 rounded-2xl bg-white border border-gray-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Mis horarios</p>

                    @php $horarios = $expediente['horarios'] ?? []; @endphp

                    @if (count($horarios) > 0)
                        <ul class="mt-3 space-y-2 text-sm text-gray-700">
                            @foreach ($horarios as $horario)
                                <li class="flex justify-between gap-3">
                                    <span class="capitalize">{{ $horario['dia_semana'] }}</span>
                                    <span class="text-gray-500">
                                        {{ substr($horario['hora_inicio'], 0, 5) }} – {{ substr($horario['hora_fin'], 0, 5) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-gray-500">Todavía no eliges tus días de entrenamiento.</p>
                    @endif

                    <a href="{{ route('alumno.horarios') }}" class="mt-4 inline-block text-sm font-semibold text-red-600 hover:text-red-500">
                        Elegir horarios →
                    </a>
                </div>

                <!-- Próximas clases reservadas -->
                <div class="p-6 rounded-2xl bg-white border border-gray-200">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Próximas clases</p>

                    @if (count($asistencias) > 0)
                        <ul class="mt-3 space-y-2 text-sm text-gray-700">
                            @foreach ($asistencias as $asistencia)
                                <li class="flex justify-between gap-3">
                                    <span>{{ Carbon::parse($asistencia['fecha'])->format('d/m/Y') }}</span>
                                    <span class="text-gray-500 capitalize">
                                        {{ $asistencia['horario']['dia_semana'] ?? 'libre' }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-3 text-sm text-gray-500">No tienes fechas reservadas.</p>
                    @endif

                    <a href="{{ route('alumno.asistencias') }}" class="mt-4 inline-block text-sm font-semibold text-red-600 hover:text-red-500">
                        Reservar fechas →
                    </a>
                </div>

                <!-- Tienda -->
                <div class="p-6 rounded-2xl bg-gray-900 text-white">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Tienda del dojang</p>
                    <p class="mt-2 text-sm text-gray-300">
                        Uniformes, protecciones y cinturones. La compra se registra a tu nombre y el
                        cobro se cierra en el módulo de pagos.
                    </p>
                    <a href="{{ $urlTienda }}"
                       class="mt-4 inline-block px-5 py-3 rounded-lg bg-red-600 hover:bg-red-500 text-sm font-semibold">
                        Ir a comprar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Contador en vivo hacia el fin del periodo pagado
        document.querySelectorAll('[data-cuenta-regresiva]').forEach((nodo) => {
            const limite = new Date(nodo.dataset.cuentaRegresiva);

            const pintar = () => {
                const restante = limite - new Date();

                if (restante <= 0) {
                    nodo.textContent = 'Tu mensualidad venció.';
                    return;
                }

                const dias = Math.floor(restante / 86400000);
                const horas = Math.floor((restante % 86400000) / 3600000);
                const minutos = Math.floor((restante % 3600000) / 60000);
                const segundos = Math.floor((restante % 60000) / 1000);

                nodo.textContent = `${dias}d ${horas}h ${minutos}m ${segundos}s restantes`;
            };

            pintar();
            setInterval(pintar, 1000);
        });
    </script>
</x-app-layout>
