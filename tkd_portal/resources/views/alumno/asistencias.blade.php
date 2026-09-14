@php use Illuminate\Support\Carbon; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis fechas de asistencia</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-flash :avisos="$avisos" />

            <div class="grid lg:grid-cols-3 gap-6">

                <div class="p-6 bg-white rounded-2xl border border-gray-200">
                    <h3 class="font-semibold text-gray-800">Reservar una clase</h3>
                    <p class="mt-1 text-sm text-gray-500">Elige la fecha y, si quieres, el grupo al que asistirás.</p>

                    <form method="POST" action="{{ route('alumno.asistencias.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="fecha" value="Fecha" />
                            <x-text-input id="fecha" class="block mt-1 w-full" type="date" name="fecha"
                                          :value="old('fecha', Carbon::today()->toDateString())"
                                          min="{{ Carbon::today()->toDateString() }}" required />
                        </div>

                        <div>
                            <x-input-label for="horario_id" value="Grupo" />
                            <select id="horario_id" name="horario_id"
                                    class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">
                                <option value="">Sin grupo específico</option>
                                @foreach ($horarios as $horario)
                                    <option value="{{ $horario['id'] }}" @selected(old('horario_id') == $horario['id'])>
                                        {{ ucfirst($horario['dia_semana']) }}
                                        {{ substr($horario['hora_inicio'], 0, 5) }}–{{ substr($horario['hora_fin'], 0, 5) }}
                                    </option>
                                @endforeach
                            </select>
                            @if (count($horarios) === 0)
                                <p class="mt-2 text-xs text-amber-700">
                                    Todavía no tienes horarios asignados.
                                    <a href="{{ route('alumno.horarios') }}" class="underline">Elígelos aquí.</a>
                                </p>
                            @endif
                        </div>

                        <x-primary-button>Reservar</x-primary-button>
                    </form>
                </div>

                <div class="lg:col-span-2 space-y-6">
                    <div class="p-6 bg-white rounded-2xl border border-gray-200">
                        <h3 class="font-semibold text-gray-800">Próximas clases ({{ count($proximas) }})</h3>

                        @if (count($proximas) > 0)
                            <ul class="mt-4 divide-y divide-gray-100">
                                @foreach ($proximas as $asistencia)
                                    <li class="py-3 flex items-center justify-between gap-3">
                                        <div>
                                            <p class="font-medium text-gray-800">
                                                {{ Carbon::parse($asistencia['fecha'])->format('d/m/Y') }}
                                            </p>
                                            <p class="text-sm text-gray-500 capitalize">
                                                @if (! empty($asistencia['horario']))
                                                    {{ $asistencia['horario']['dia_semana'] }}
                                                    {{ substr($asistencia['horario']['hora_inicio'], 0, 5) }}–{{ substr($asistencia['horario']['hora_fin'], 0, 5) }}
                                                @else
                                                    Sin grupo específico
                                                @endif
                                            </p>
                                        </div>

                                        <form method="POST" action="{{ route('alumno.asistencias.destroy', $asistencia['id']) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-500">
                                                Cancelar
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-4 text-sm text-gray-500">No tienes clases reservadas.</p>
                        @endif
                    </div>

                    <div class="p-6 bg-white rounded-2xl border border-gray-200">
                        <h3 class="font-semibold text-gray-800">Historial reciente</h3>

                        @if (count($pasadas) > 0)
                            <ul class="mt-4 grid sm:grid-cols-2 gap-2 text-sm text-gray-600">
                                @foreach ($pasadas as $asistencia)
                                    <li class="flex justify-between gap-3 px-3 py-2 rounded-lg bg-gray-50">
                                        <span>{{ Carbon::parse($asistencia['fecha'])->format('d/m/Y') }}</span>
                                        <span class="capitalize text-gray-500">{{ $asistencia['estado'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-4 text-sm text-gray-500">Aún no hay clases pasadas registradas.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
