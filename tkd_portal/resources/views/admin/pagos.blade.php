@php use Illuminate\Support\Carbon; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Control de pagos</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-flash :avisos="$avisos" />

            <div class="grid lg:grid-cols-2 gap-6">

                <!-- Registrar mensualidad -->
                <div class="p-6 bg-white rounded-2xl border border-gray-200">
                    <h3 class="font-semibold text-gray-800">Registrar mensualidad</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        El cobro se guarda en el módulo de pagos y actualiza el indicador del alumno.
                    </p>

                    <form method="POST" action="{{ route('admin.pagos.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2"
                          x-data="{ metodo: '{{ old('metodo_pago', 'efectivo') }}' }">
                        @csrf

                        <div>
                            <x-input-label for="alumno_id" value="Alumno" />
                            <select id="alumno_id" name="alumno_id" required
                                    class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">
                                @foreach ($alumnado as $alumno)
                                    <option value="{{ $alumno['id'] }}" @selected(old('alumno_id', $consultaId) == $alumno['id'])>
                                        #{{ $alumno['id'] }} · {{ $alumno['nombre'] }} {{ $alumno['apellido_paterno'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="monto" value="Monto" />
                            <x-text-input id="monto" class="block mt-1 w-full" type="number" step="0.01" name="monto"
                                          :value="old('monto', number_format($mensualidad, 2, '.', ''))" required />
                        </div>

                        <div>
                            <x-input-label for="metodo_pago" value="Método" />
                            <select id="metodo_pago" name="metodo_pago" x-model="metodo" required
                                    class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>

                        <div x-show="metodo === 'transferencia'" x-cloak>
                            <x-input-label for="numero_rastreo" value="Número de rastreo" />
                            <x-text-input id="numero_rastreo" class="block mt-1 w-full" type="text" name="numero_rastreo"
                                          :value="old('numero_rastreo')" />
                        </div>

                        <div>
                            <x-input-label for="ciclo_pago" value="Ciclo" />
                            <select id="ciclo_pago" name="ciclo_pago" required
                                    class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">
                                <option value="mes" @selected(old('ciclo_pago', 'mes') === 'mes')>Mensual</option>
                                <option value="quincena" @selected(old('ciclo_pago') === 'quincena')>Quincenal</option>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="periodo_cubierto" value="Periodo cubierto" />
                            <x-text-input id="periodo_cubierto" class="block mt-1 w-full" type="date" name="periodo_cubierto"
                                          :value="old('periodo_cubierto', Carbon::today()->startOfMonth()->toDateString())" required />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="fecha_pago" value="Fecha de pago" />
                            <x-text-input id="fecha_pago" class="block mt-1 w-full" type="date" name="fecha_pago"
                                          :value="old('fecha_pago', Carbon::today()->toDateString())" required />
                        </div>

                        <div class="sm:col-span-2">
                            <x-primary-button>Registrar pago</x-primary-button>
                        </div>
                    </form>
                </div>

                <!-- Consultar estado de cuenta -->
                <div class="p-6 bg-white rounded-2xl border border-gray-200">
                    <h3 class="font-semibold text-gray-800">Estado de cuenta</h3>

                    <form method="GET" action="{{ route('admin.pagos') }}" class="mt-4 flex gap-2 items-end">
                        <div class="flex-1">
                            <x-input-label for="consulta" value="Alumno" />
                            <select id="consulta" name="alumno_id"
                                    class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">
                                <option value="">Selecciona…</option>
                                @foreach ($alumnado as $alumno)
                                    <option value="{{ $alumno['id'] }}" @selected($consultaId === (int) $alumno['id'])>
                                        #{{ $alumno['id'] }} · {{ $alumno['nombre'] }} {{ $alumno['apellido_paterno'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button>Consultar</x-primary-button>
                    </form>

                    @if ($consulta)
                        @php $estadoPago = $consulta['estado_cuenta'] ?? 'desconocido'; @endphp

                        <div class="mt-6 grid sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl text-center
                                {{ $estadoPago === 'al_dia' ? 'bg-green-50 text-green-800' : ($estadoPago === 'con_adeudo' ? 'bg-red-50 text-red-800' : 'bg-gray-50 text-gray-700') }}">
                                <p class="text-xs uppercase tracking-wider">Indicador de pago</p>
                                <p class="mt-1 text-xl font-bold">
                                    {{ $estadoPago === 'al_dia' ? 'Pagado' : ($estadoPago === 'con_adeudo' ? 'No ha pagado' : 'Sin pagos') }}
                                </p>
                            </div>
                            <div class="p-4 rounded-xl bg-gray-50 text-sm text-gray-600">
                                <p><strong>Último periodo:</strong> {{ $consulta['ultimo_periodo_pagado'] ?? '—' }}</p>
                                <p class="mt-1"><strong>Próximo vto:</strong> {{ $consulta['proximo_vencimiento'] ?? '—' }}</p>
                                <p class="mt-1"><strong>Días restantes:</strong> {{ $consulta['dias_restantes'] ?? '—' }}</p>
                            </div>
                        </div>

                        @if (count($consulta['pagos_pendientes'] ?? []) > 0)
                            <div class="mt-6">
                                <h4 class="font-semibold text-gray-800 text-sm">Pendientes de confirmar</h4>
                                <ul class="mt-2 space-y-2">
                                    @foreach ($consulta['pagos_pendientes'] as $pendiente)
                                        <li class="flex items-center justify-between gap-3 p-3 rounded-lg bg-amber-50 text-sm">
                                            <span>
                                                Periodo {{ Carbon::parse($pendiente['periodo_cubierto'])->format('d/m/Y') }} ·
                                                ${{ number_format((float) $pendiente['monto'], 2) }} ·
                                                {{ $pendiente['metodo_pago'] }}
                                            </span>
                                            <form method="POST" action="{{ route('admin.pagos.confirmar', $pendiente['id']) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-500">
                                                    Confirmar cobro
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="mt-6">
                            <h4 class="font-semibold text-gray-800 text-sm">Historial</h4>
                            <div class="mt-2 overflow-x-auto">
                                <table class="w-full text-sm text-left">
                                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase">
                                        <tr>
                                            <th class="px-3 py-2">Periodo</th>
                                            <th class="px-3 py-2">Monto</th>
                                            <th class="px-3 py-2">Método</th>
                                            <th class="px-3 py-2">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100">
                                        @forelse ($consulta['historial'] ?? [] as $pago)
                                            <tr>
                                                <td class="px-3 py-2">{{ Carbon::parse($pago['periodo_cubierto'])->format('d/m/Y') }}</td>
                                                <td class="px-3 py-2">${{ number_format((float) $pago['monto'], 2) }}</td>
                                                <td class="px-3 py-2 capitalize">
                                                    {{ $pago['metodo_pago'] }}
                                                    @if (! empty($pago['numero_rastreo']))
                                                        <span class="block text-xs text-gray-400">{{ $pago['numero_rastreo'] }}</span>
                                                    @endif
                                                </td>
                                                <td class="px-3 py-2 capitalize">{{ $pago['estado'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-3 py-6 text-center text-gray-500">Sin pagos registrados.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <p class="mt-6 text-sm text-gray-500">Elige un alumno para ver su historial y su vigencia.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
