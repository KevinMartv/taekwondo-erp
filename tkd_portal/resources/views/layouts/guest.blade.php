<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TKD ERP') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @include('layouts.assets')
    </head>
    <body class="font-sans text-gray-900 antialiased bg-gray-900">
        <div class="min-h-screen flex flex-col sm:justify-center items-center py-10 px-4">
            <a href="{{ route('welcome') }}" class="flex items-center gap-3 text-white">
                <span class="grid place-items-center w-12 h-12 rounded-full bg-red-600 font-bold text-lg">태</span>
                <span class="text-xl font-semibold tracking-wide">Dojang ERP</span>
            </a>

            <div class="w-full sm:max-w-md mt-6 px-6 py-6 bg-white shadow-xl overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>

            <a href="{{ route('welcome') }}" class="mt-6 text-sm text-gray-400 hover:text-gray-200">
                ← Volver al inicio
            </a>
        </div>
    </body>
</html>
