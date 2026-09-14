@extends('layouts.pagos')

@section('title', 'Pagar '.$pago['referencia'])

@section('content')
    <section class="pay-layout">
        <form method="post" action="{{ route('pagos.store') }}" class="pay-card" autocomplete="off">
            @csrf
            <input type="hidden" name="venta_id" value="{{ $pago['venta_id'] }}">
            <input type="hidden" name="referencia" value="{{ $pago['referencia'] }}">
            <input type="hidden" name="total" value="{{ $pago['total'] }}">
            <input type="hidden" name="token" value="{{ $pago['token'] }}">
            <input type="hidden" name="return_url" value="{{ $pago['return_url'] }}">

            <h1>¿Cómo quieres pagar?</h1>
            <p class="pay-muted">Orden {{ $pago['referencia'] }} · venta #{{ $pago['venta_id'] }}</p>

            <label class="pay-option">
                <input type="radio" name="metodo_pago" value="tarjeta" {{ old('metodo_pago', 'tarjeta') === 'tarjeta' ? 'checked' : '' }}>
                <span>Tarjeta de crédito o débito</span>
            </label>

            <div class="pay-fields">
                <label>Titular
                    <input type="text" name="titular" value="{{ old('titular') }}" placeholder="Nombre como aparece en la tarjeta">
                </label>
                <label>Número
                    <input type="text" name="numero" inputmode="numeric" placeholder="4242424242424242" maxlength="19">
                </label>
                <div class="pay-row">
                    <label>Vencimiento
                        <input type="text" name="vencimiento" placeholder="MM/AA" maxlength="5">
                    </label>
                    <label>CVV
                        <input type="password" name="cvv" maxlength="4" autocomplete="off">
                    </label>
                </div>
            </div>

            <label class="pay-option">
                <input type="radio" name="metodo_pago" value="transferencia" {{ old('metodo_pago') === 'transferencia' ? 'checked' : '' }}>
                <span>Transferencia bancaria</span>
            </label>

            <button class="pay-btn" type="submit">Pagar ${{ number_format((float) $pago['total'], 2) }} MXN</button>
            <p class="pay-muted">Al pagar confirmamos la orden en el módulo POS y regresas a tu compra.</p>
        </form>

        <aside class="pay-summary">
            <h2>Resumen</h2>
            <p><span>Productos</span> <strong>${{ number_format((float) $pago['total'], 2) }}</strong></p>
            <p class="pay-total"><span>Total</span> <strong>${{ number_format((float) $pago['total'], 2) }} MXN</strong></p>
        </aside>
    </section>
@endsection
