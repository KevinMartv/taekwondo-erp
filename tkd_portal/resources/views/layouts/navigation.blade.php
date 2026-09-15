<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <span class="grid place-items-center w-9 h-9 rounded-full bg-red-600 text-white font-bold text-sm">태</span>
                        <span class="font-semibold text-gray-800 hidden lg:inline">Dojang ERP</span>
                    </a>
                </div>

                <!-- Navegación por nivel de acceso -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @can('is-admin')
                        <x-nav-link :href="route('admin.alumnos')" :active="request()->routeIs('admin.alumnos*')">
                            {{ __('Alumnos') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.pagos')" :active="request()->routeIs('admin.pagos*')">
                            {{ __('Pagos') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.inventario')" :active="request()->routeIs('admin.inventario')">
                            {{ __('Inventario POS') }}
                        </x-nav-link>
                    @endcan

                    @can('is-alumno')
                        <x-nav-link :href="route('alumno.mi_cuenta')" :active="request()->routeIs('alumno.mi_cuenta')">
                            {{ __('Mi cuenta') }}
                        </x-nav-link>
                        @if (auth()->user()->expedienteId())
                            <x-nav-link :href="route('alumno.perfil')" :active="request()->routeIs('alumno.perfil')">
                                {{ __('Mi perfil') }}
                            </x-nav-link>
                            <x-nav-link :href="route('alumno.horarios')" :active="request()->routeIs('alumno.horarios')">
                                {{ __('Horarios') }}
                            </x-nav-link>
                            <x-nav-link :href="route('alumno.asistencias')" :active="request()->routeIs('alumno.asistencias')">
                                {{ __('Asistencia') }}
                            </x-nav-link>
                            <x-nav-link :href="route('alumno.tienda')" :active="false">
                                {{ __('Tienda') }}
                            </x-nav-link>
                        @endif
                    @endcan
                </div>
            </div>

            <!-- Menú de la cuenta -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <span class="ms-2 px-2 py-0.5 rounded-full text-xs {{ Auth::user()->esAdmin() ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ Auth::user()->esAdmin() ? 'Administrador' : 'Alumno' }}
                            </span>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Datos de acceso') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Cerrar sesión') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburguesa -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Navegación móvil -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @can('is-admin')
                <x-responsive-nav-link :href="route('admin.alumnos')" :active="request()->routeIs('admin.alumnos*')">
                    {{ __('Alumnos') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.pagos')" :active="request()->routeIs('admin.pagos*')">
                    {{ __('Pagos') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.inventario')" :active="request()->routeIs('admin.inventario')">
                    {{ __('Inventario POS') }}
                </x-responsive-nav-link>
            @endcan

            @can('is-alumno')
                <x-responsive-nav-link :href="route('alumno.mi_cuenta')" :active="request()->routeIs('alumno.mi_cuenta')">
                    {{ __('Mi cuenta') }}
                </x-responsive-nav-link>
                @if (auth()->user()->expedienteId())
                    <x-responsive-nav-link :href="route('alumno.perfil')" :active="request()->routeIs('alumno.perfil')">
                        {{ __('Mi perfil') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('alumno.horarios')" :active="request()->routeIs('alumno.horarios')">
                        {{ __('Horarios') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('alumno.asistencias')" :active="request()->routeIs('alumno.asistencias')">
                        {{ __('Asistencia') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('alumno.tienda')" :active="false">
                        {{ __('Tienda') }}
                    </x-responsive-nav-link>
                @endif
            @endcan
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Datos de acceso') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Cerrar sesión') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
