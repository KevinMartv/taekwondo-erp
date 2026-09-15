@php use Illuminate\Support\Carbon; @endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de alumnos</h2>
            <div class="flex gap-2 text-sm">
                <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700">{{ $resumen['total'] }} en lista</span>
                <span class="px-3 py-1 rounded-full bg-green-100 text-green-700">{{ $resumen['activos'] }} activos</span>
                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700">{{ $resumen['al_dia'] }} al día</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-flash :avisos="$avisos" />

            <form method="GET" action="{{ route('admin.alumnos') }}" class="p-4 bg-white rounded-2xl border border-gray-200 flex flex-wrap gap-3 items-end">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="q" value="Buscar" />
                    <x-text-input id="q" name="q" type="search" class="block mt-1 w-full" :value="$busqueda" placeholder="Nombre o número de expediente" />
                </div>
                <div>
                    <x-input-label for="estado" value="Filtro" />
                    <select id="estado" name="estado" class="block mt-1 border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">
                        <option value="todos" @selected($filtro === 'todos')>Todos</option>
                        <option value="activos" @selected($filtro === 'activos')>Activos</option>
                        <option value="suspendidos" @selected($filtro === 'suspendidos')>Suspendidos</option>
                        <option value="con_adeudo" @selected($filtro === 'con_adeudo')>Con adeudo</option>
                    </select>
                </div>
                <x-primary-button>Aplicar</x-primary-button>
                <a href="{{ route('admin.alumnos') }}" class="text-sm text-gray-500 hover:text-gray-700 pb-2">Limpiar</a>
            </form>

            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
                            <tr>
                                <th class="px-4 py-3">Alumno</th>
                                <th class="px-4 py-3">Grado</th>
                                <th class="px-4 py-3">Horarios</th>
                                <th class="px-4 py-3">Suscripción</th>
                                <th class="px-4 py-3">Inscripción</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($alumnado as $alumno)
                                @php
                                    $suscripcion = $alumno['suscripcion'] ?? [];
                                    $estadoPago = $suscripcion['estado_cuenta'] ?? 'desconocido';
                                    $etiquetaPago = match ($estadoPago) {
                                        'al_dia' => ['Pagado', 'bg-green-100 text-green-700'],
                                        'con_adeudo' => ['No ha pagado', 'bg-red-100 text-red-700'],
                                        'sin_historial' => ['Sin pagos', 'bg-gray-100 text-gray-600'],
                                        default => ['Sin dato', 'bg-gray-100 text-gray-500'],
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-800">
                                            {{ $alumno['nombre'] }} {{ $alumno['apellido_paterno'] }} {{ $alumno['apellido_materno'] }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            #{{ $alumno['id'] }} · {{ $alumno['telefono_contacto'] }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $alumno['nivel']['nombre'] ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-600">
                                        @forelse ($alumno['horarios'] ?? [] as $horario)
                                            <span class="inline-block px-2 py-0.5 mb-1 rounded bg-gray-100 text-xs capitalize">
                                                {{ substr($horario['dia_semana'], 0, 3) }} {{ substr($horario['hora_inicio'], 0, 5) }}
                                            </span>
                                        @empty
                                            <span class="text-xs text-gray-400">Sin horarios</span>
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $etiquetaPago[1] }}">
                                            {{ $etiquetaPago[0] }}
                                        </span>
                                        @if (! empty($suscripcion['proximo_vencimiento']))
                                            <p class="mt-1 text-xs text-gray-500">
                                                Vence {{ Carbon::parse($suscripcion['proximo_vencimiento'])->format('d/m/Y') }}
                                            </p>
                                        @endif
                                        @if (count($suscripcion['pagos_pendientes'] ?? []) > 0)
                                            <p class="mt-1 text-xs text-amber-700">
                                                {{ count($suscripcion['pagos_pendientes']) }} por confirmar
                                            </p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ ($alumno['activo'] ?? false) ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                            {{ ($alumno['activo'] ?? false) ? 'Activo' : 'Suspendido' }}
                                        </span>
                                        <p class="mt-1 text-xs text-gray-500">
                                            Desde {{ Carbon::parse($alumno['fecha_ingreso'])->format('d/m/Y') }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-2 justify-end">
                                            <a href="{{ route('admin.alumnos.edit', $alumno['id']) }}"
                                               class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                                Editar
                                            </a>

                                            <a href="{{ route('admin.pagos', ['alumno_id' => $alumno['id']]) }}"
                                               class="px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-semibold text-gray-700 hover:bg-gray-100">
                                                Pagos
                                            </a>

                                            <form method="POST" action="{{ route('admin.alumnos.estado', $alumno['id']) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="activo" value="{{ ($alumno['activo'] ?? false) ? 0 : 1 }}">
                                                <button type="submit"
                                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ ($alumno['activo'] ?? false) ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
                                                    {{ ($alumno['activo'] ?? false) ? 'Suspender' : 'Activar' }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.alumnos.destroy', $alumno['id']) }}"
                                                  onsubmit="return confirm('¿Borrar definitivamente el expediente #{{ $alumno['id'] }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="px-3 py-1.5 rounded-lg bg-red-100 text-red-700 text-xs font-semibold hover:bg-red-200">
                                                    Borrar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                                        No hay alumnos que coincidan con el filtro.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="text-xs text-gray-500">
                El expediente vive en el módulo de alumnos y el indicador de pago viene del módulo de
                pagos. Un alumno con mensualidades registradas no se puede borrar: suspéndelo.
            </p>
        </div>
    </div>
</x-app-layout>
