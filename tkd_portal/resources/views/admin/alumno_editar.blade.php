@php use Illuminate\Support\Carbon; @endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Expediente #{{ $expediente['id'] }} · {{ $expediente['nombre'] }} {{ $expediente['apellido_paterno'] }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-flash />

            <div class="grid lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 p-6 bg-white rounded-2xl border border-gray-200">
                    <h3 class="font-semibold text-gray-800">Datos del alumno</h3>

                    <form method="POST" action="{{ route('admin.alumnos.update', $expediente['id']) }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                        @csrf
                        @method('PUT')

                        <div class="sm:col-span-2">
                            <x-input-label for="nombre" value="Nombre(s)" />
                            <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre"
                                          :value="old('nombre', $expediente['nombre'])" required />
                        </div>

                        <div>
                            <x-input-label for="apellido_paterno" value="Apellido paterno" />
                            <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno"
                                          :value="old('apellido_paterno', $expediente['apellido_paterno'])" required />
                        </div>

                        <div>
                            <x-input-label for="apellido_materno" value="Apellido materno" />
                            <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno"
                                          :value="old('apellido_materno', $expediente['apellido_materno'])" />
                        </div>

                        <div>
                            <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
                            <x-text-input id="fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento"
                                          :value="old('fecha_nacimiento', Carbon::parse($expediente['fecha_nacimiento'])->toDateString())" required />
                        </div>

                        <div>
                            <x-input-label for="telefono_contacto" value="Teléfono" />
                            <x-text-input id="telefono_contacto" class="block mt-1 w-full" type="tel" name="telefono_contacto"
                                          :value="old('telefono_contacto', $expediente['telefono_contacto'])" required />
                        </div>

                        <div>
                            <x-input-label for="nivel_id" value="Grado (cinturón)" />
                            <select id="nivel_id" name="nivel_id" required
                                    class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">
                                @foreach ($niveles as $nivel)
                                    <option value="{{ $nivel['id'] }}" @selected(old('nivel_id', $expediente['nivel_id']) == $nivel['id'])>
                                        {{ $nivel['nombre'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="fecha_ingreso" value="Fecha de ingreso" />
                            <x-text-input id="fecha_ingreso" class="block mt-1 w-full" type="date" name="fecha_ingreso"
                                          :value="old('fecha_ingreso', Carbon::parse($expediente['fecha_ingreso'])->toDateString())" required />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label value="Horarios asignados" />
                            <div class="mt-2 grid sm:grid-cols-2 gap-2">
                                @foreach ($horarios as $horario)
                                    <label class="flex items-center gap-2 p-3 rounded-lg border border-gray-200 text-sm">
                                        <input type="checkbox" name="horarios[]" value="{{ $horario['id'] }}"
                                               @checked(in_array($horario['id'], $seleccionados))
                                               class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                        <span class="capitalize">
                                            {{ $horario['dia_semana'] }}
                                            {{ substr($horario['hora_inicio'], 0, 5) }}–{{ substr($horario['hora_fin'], 0, 5) }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="sm:col-span-2 flex items-center gap-3">
                            <x-primary-button>Guardar expediente</x-primary-button>
                            <a href="{{ route('admin.alumnos') }}" class="text-sm text-gray-500 hover:text-gray-700">Volver al listado</a>
                        </div>
                    </form>
                </div>

                <div class="space-y-6">
                    <div class="p-6 bg-white rounded-2xl border border-gray-200">
                        <h3 class="font-semibold text-gray-800">Suscripción</h3>

                        @php $estadoPago = $suscripcion['estado_cuenta'] ?? 'desconocido'; @endphp

                        <p class="mt-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold
                                {{ $estadoPago === 'al_dia' ? 'bg-green-100 text-green-700' : ($estadoPago === 'con_adeudo' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $estadoPago === 'al_dia' ? 'Pagado' : ($estadoPago === 'con_adeudo' ? 'No ha pagado' : 'Sin pagos') }}
                            </span>
                        </p>

                        @if (! empty($suscripcion['proximo_vencimiento']))
                            <p class="mt-3 text-sm text-gray-600">
                                Vence el {{ Carbon::parse($suscripcion['proximo_vencimiento'])->format('d/m/Y') }}
                                ({{ $suscripcion['dias_restantes'] }} días)
                            </p>
                        @endif

                        <a href="{{ route('admin.pagos', ['alumno_id' => $expediente['id']]) }}"
                           class="mt-4 inline-block text-sm font-semibold text-red-600 hover:text-red-500">
                            Ver y registrar pagos →
                        </a>
                    </div>

                    <div class="p-6 bg-white rounded-2xl border border-gray-200">
                        <h3 class="font-semibold text-gray-800">Estado de inscripción</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ ($expediente['activo'] ?? false) ? 'El alumno puede entrenar y comprar.' : 'El alumno está suspendido.' }}
                        </p>

                        <form method="POST" action="{{ route('admin.alumnos.estado', $expediente['id']) }}" class="mt-4">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="activo" value="{{ ($expediente['activo'] ?? false) ? 0 : 1 }}">
                            <button type="submit"
                                    class="px-4 py-2 rounded-lg text-sm font-semibold {{ ($expediente['activo'] ?? false) ? 'bg-amber-100 text-amber-800 hover:bg-amber-200' : 'bg-green-100 text-green-800 hover:bg-green-200' }}">
                                {{ ($expediente['activo'] ?? false) ? 'Suspender alumno' : 'Activar alumno' }}
                            </button>
                        </form>
                    </div>

                    <div class="p-6 bg-white rounded-2xl border border-gray-200">
                        <h3 class="font-semibold text-gray-800">Cuenta del portal</h3>
                        @if ($cuenta)
                            <p class="mt-2 text-sm text-gray-600">{{ $cuenta->email }}</p>
                            <p class="text-xs text-gray-400">Nivel: {{ $cuenta->role }}</p>
                        @else
                            <p class="mt-2 text-sm text-gray-500">Este expediente no tiene cuenta de acceso vinculada.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
