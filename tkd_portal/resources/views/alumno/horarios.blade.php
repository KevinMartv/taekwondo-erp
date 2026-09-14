<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mis horarios de entrenamiento</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-flash :avisos="$avisos" />

            <div class="p-6 bg-white rounded-2xl border border-gray-200">
                <h3 class="font-semibold text-gray-800">Elige los grupos a los que asistes</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Marca los días y horas de tu grupo. Después podrás reservar fechas concretas de clase.
                </p>

                @if (count($horarios) > 0)
                    <form method="POST" action="{{ route('alumno.horarios.update') }}" class="mt-5">
                        @csrf
                        @method('PUT')

                        <div class="grid sm:grid-cols-2 gap-3">
                            @foreach ($horarios as $horario)
                                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 hover:border-gray-300 cursor-pointer">
                                    <input type="checkbox" name="horarios[]" value="{{ $horario['id'] }}"
                                           @checked(in_array($horario['id'], $seleccionados))
                                           class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                    <span>
                                        <span class="block font-medium text-gray-800 capitalize">{{ $horario['dia_semana'] }}</span>
                                        <span class="block text-sm text-gray-500">
                                            {{ substr($horario['hora_inicio'], 0, 5) }} – {{ substr($horario['hora_fin'], 0, 5) }}
                                            @if (! empty($horario['cupo_maximo']))
                                                · cupo {{ $horario['cupo_maximo'] }}
                                            @endif
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-6 flex items-center gap-3">
                            <x-primary-button>Guardar mis horarios</x-primary-button>
                            <a href="{{ route('alumno.mi_cuenta') }}" class="text-sm text-gray-500 hover:text-gray-700">Volver</a>
                        </div>
                    </form>
                @else
                    <p class="mt-4 text-sm text-gray-500">No hay horarios publicados por la escuela.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
