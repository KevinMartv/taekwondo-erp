<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tienda e inventario POS') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <p class="mb-4 text-sm text-gray-600">
                Catálogo Dojang Market. También puedes abrirlo en
                <a class="text-blue-600 underline" href="{{ config('services.modules.pos.url') }}" target="_blank" rel="noopener">ventana completa</a>.
            </p>
            <iframe
                title="Catálogo POS"
                src="{{ rtrim(config('services.modules.pos.url'), '/') }}"
                class="w-full min-h-[80vh] bg-white rounded-lg border border-gray-200"
            ></iframe>
        </div>
    </div>
</x-app-layout>
