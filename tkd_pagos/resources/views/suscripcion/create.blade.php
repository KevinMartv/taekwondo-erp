@extends('layouts.pagos')

@section('title', 'Renovar suscripción')

@section('content')
    <section class="pay-layout">
        <form method="post" action="{{ route('suscripcion.store') }}" class="pay-card" autocomplete="off">
            @csrf
            <input type="hidden" name="alumno_id" value="{{ $intencion['alumno_id'] }}">
            <input type="hidden" name="alumno" value="{{ $intencion['alumno'] }}">
            <input type="hidden" name="periodo" value="{{ $intencion['periodo'] }}">
            <input type="hidden" name="monto" value="{{ $intencion['monto'] }}">
            <input type="hidden" name="token" value="{{ $intencion['token'] }}">
            <input type="hidden" name="return_url" value="{{ $intencion['return_url'] }}">

            <h1>Renovar mensualidad</h1>
            <p class="pay-muted">
                {{ $intencion['alumno'] }} · alumno #{{ $intencion['alumno_id'] }} ·
                periodo {{ \Carbon\Carbon::parse($intencion['periodo'])->translatedFormat('d/m/Y') }}
            </p>

            <label class="pay-option">
                <input type="radio" name="metodo_pago" value="transferencia" {{ old('metodo_pago', 'transferencia') === 'transferencia' ? 'checked' : '' }}>
                <span>Tarjeta o transferencia (se acredita al instante)</span>
            </label>

            <div class="pay-fields">
                <label>Titular
                    <input type="text" name="titular" value="{{ old('titular', $intencion['alumno']) }}" placeholder="Nombre del titular">
                </label>
                <label>Número
                    <input type="text" name="numero" value="{{ old('numero') }}" inputmode="numeric" placeholder="4242 4242 4242 4242" maxlength="23">
                </label>
                <div class="pay-row">
                    <label>Vencimiento
                        <input type="text" name="vencimiento" value="{{ old('vencimiento') }}" placeholder="MM/AA" maxlength="5">
                    </label>
                    <label>CVV
                        <input type="password" name="cvv" maxlength="4" autocomplete="off">
                    </label>
                </div>
            </div>

            <label class="pay-option">
                <input type="radio" name="metodo_pago" value="efectivo" {{ old('metodo_pago') === 'efectivo' ? 'checked' : '' }}>
                <span>Efectivo en recepción (queda pendiente de confirmar)</span>
            </label>

            <button class="pay-btn" type="submit">Pagar ${{ number_format((float) $intencion['monto'], 2) }} MXN</button>
            <p class="pay-muted">Al confirmar registramos el cobro y regresas a tu cuenta del portal.</p>
        </form>

        <aside class="pay-summary">
            <h2>Resumen</h2>
            <p><span>Mensualidad</span> <strong>${{ number_format((float) $intencion['monto'], 2) }}</strong></p>
            <p><span>Periodo</span> <strong>{{ \Carbon\Carbon::parse($intencion['periodo'])->translatedFormat('M Y') }}</strong></p>
            <p class="pay-total"><span>Total</span> <strong>${{ number_format((float) $intencion['monto'], 2) }} MXN</strong></p>
        </aside>
    </section>
@endsection
