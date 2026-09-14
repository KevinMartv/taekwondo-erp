<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mi perfil</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <x-flash :avisos="$avisos" />

            <div class="p-6 bg-white rounded-2xl border border-gray-200">
                <h3 class="font-semibold text-gray-800">Datos de mi expediente</h3>
                <p class="mt-1 text-sm text-gray-500">
                    Puedes actualizar tus datos personales. El grado (cinturón) y el estado de tu
                    inscripción los administra tu instructor.
                </p>

                @if ($expediente)
                    <form method="POST" action="{{ route('alumno.perfil.update') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
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
                                          :value="old('fecha_nacimiento', \Illuminate\Support\Carbon::parse($expediente['fecha_nacimiento'])->toDateString())" required />
                        </div>

                        <div>
                            <x-input-label for="telefono_contacto" value="Teléfono de contacto" />
                            <x-text-input id="telefono_contacto" class="block mt-1 w-full" type="tel" name="telefono_contacto"
                                          :value="old('telefono_contacto', $expediente['telefono_contacto'])" required />
                        </div>

                        <div class="sm:col-span-2 grid sm:grid-cols-2 gap-4 pt-2 border-t border-gray-100">
                            <div>
                                <p class="text-sm text-gray-500">Grado actual</p>
                                <p class="font-medium text-gray-800">{{ $expediente['nivel']['nombre'] ?? 'Sin asignar' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Fecha de ingreso</p>
                                <p class="font-medium text-gray-800">
                                    {{ \Illuminate\Support\Carbon::parse($expediente['fecha_ingreso'])->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>

                        <div class="sm:col-span-2 flex items-center gap-3">
                            <x-primary-button>Guardar cambios</x-primary-button>
                            <a href="{{ route('alumno.mi_cuenta') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancelar</a>
                        </div>
                    </form>
                @else
                    <p class="mt-4 text-sm text-gray-500">No pudimos cargar tu expediente en este momento.</p>
                @endif
            </div>

            <div class="p-6 bg-white rounded-2xl border border-gray-200">
                <h3 class="font-semibold text-gray-800">Datos de acceso</h3>
                <p class="mt-1 text-sm text-gray-500">Tu correo y contraseña del portal.</p>
                <a href="{{ route('profile.edit') }}" class="mt-3 inline-block text-sm font-semibold text-red-600 hover:text-red-500">
                    Administrar mi acceso →
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
