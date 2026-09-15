<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Completar mi expediente</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            <x-flash :avisos="$avisos" />

            <div class="p-6 bg-white rounded-2xl border border-gray-200">
                <p class="text-sm text-gray-500">
                    Tu cuenta todavía no está vinculada a un expediente del módulo de alumnos.
                    Completa estos datos para activar tu panel.
                </p>

                @if ($niveles === [])
                    <p class="mt-4 text-sm text-amber-700">
                        El módulo de alumnos no responde en este momento. Vuelve a intentarlo más tarde.
                    </p>
                @else
                    <form method="POST" action="{{ route('alumno.expediente.guardar') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                        @csrf

                        <div class="sm:col-span-2">
                            <x-input-label for="nombre" value="Nombre(s)" />
                            <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre"
                                          :value="old('nombre', auth()->user()->name)" required />
                        </div>

                        <div>
                            <x-input-label for="apellido_paterno" value="Apellido paterno" />
                            <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno')" required />
                        </div>

                        <div>
                            <x-input-label for="apellido_materno" value="Apellido materno" />
                            <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno')" />
                        </div>

                        <div>
                            <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
                            <x-text-input id="fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento')" required />
                        </div>

                        <div>
                            <x-input-label for="telefono_contacto" value="Teléfono de contacto" />
                            <x-text-input id="telefono_contacto" class="block mt-1 w-full" type="tel" name="telefono_contacto" :value="old('telefono_contacto')" required />
                        </div>

                        <div class="sm:col-span-2">
                            <x-input-label for="nivel_id" value="Grado actual" />
                            <select id="nivel_id" name="nivel_id" required
                                    class="block mt-1 w-full border-gray-300 focus:border-red-500 focus:ring-red-500 rounded-md shadow-sm">
                                @foreach ($niveles as $nivel)
                                    <option value="{{ $nivel['id'] }}" @selected(old('nivel_id') == $nivel['id'])>
                                        {{ $nivel['nombre'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <x-primary-button>Registrar mi expediente</x-primary-button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
