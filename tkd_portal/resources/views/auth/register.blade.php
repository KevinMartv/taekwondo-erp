<x-guest-layout>
    <h1 class="text-lg font-semibold text-gray-800">Crear cuenta de alumno</h1>
    <p class="mt-1 text-sm text-gray-500">
        Con estos datos generamos tu expediente en el módulo de alumnos.
    </p>

    @if ($niveles === [])
        <div class="mt-4 p-3 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-xs">
            El módulo de alumnos no está disponible. Puedes crear tu cuenta y completar tu
            expediente después de iniciar sesión.
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="mt-5">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="nombre" value="Nombre(s)" />
                <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus />
                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="apellido_paterno" value="Apellido paterno" />
                <x-text-input id="apellido_paterno" class="block mt-1 w-full" type="text" name="apellido_paterno" :value="old('apellido_paterno')" required />
                <x-input-error :messages="$errors->get('apellido_paterno')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="apellido_materno" value="Apellido materno" />
                <x-text-input id="apellido_materno" class="block mt-1 w-full" type="text" name="apellido_materno" :value="old('apellido_materno')" />
                <x-input-error :messages="$errors->get('apellido_materno')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
                <x-text-input id="fecha_nacimiento" class="block mt-1 w-full" type="date" name="fecha_nacimiento" :value="old('fecha_nacimiento')" required />
                <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="telefono_contacto" value="Teléfono de contacto" />
                <x-text-input id="telefono_contacto" class="block mt-1 w-full" type="tel" name="telefono_contacto" :value="old('telefono_contacto')" required />
                <x-input-error :messages="$errors->get('telefono_contacto')" class="mt-2" />
            </div>

            @if ($niveles !== [])
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
                    <x-input-error :messages="$errors->get('nivel_id')" class="mt-2" />
                </div>
            @endif

            <div class="sm:col-span-2 border-t border-gray-100 pt-4">
                <x-input-label for="email" value="Correo electrónico" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Contraseña" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Confirmar contraseña" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                Ya tengo cuenta
            </a>

            <x-primary-button>
                Crear mi cuenta
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
