{{--
    Si los assets están compilados usamos Vite; si no (instalación sin npm),
    caemos al CDN para que el portal se vea bien igualmente.
--}}
@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: Figtree, ui-sans-serif, system-ui, sans-serif; }
    </style>
@endif
