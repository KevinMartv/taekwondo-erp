@props(['avisos' => []])

@if (session('status'))
    <div class="mb-4 p-4 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
        {{ session('error') }}
    </div>
@endif

@if (session('aviso'))
    <div class="mb-4 p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
        {{ session('aviso') }}
    </div>
@endif

@foreach ($avisos as $aviso)
    <div class="mb-4 p-4 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-sm">
        {{ $aviso }}
    </div>
@endforeach

@if ($errors->any())
    <div class="mb-4 p-4 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
