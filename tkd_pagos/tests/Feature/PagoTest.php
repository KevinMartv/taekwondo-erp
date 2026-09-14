<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PagoTest extends TestCase
{
    public function test_muestra_el_formulario_de_pago_con_token_valido(): void
    {
        $payload = $this->payload();

        $this->get('/pagar?'.http_build_query($payload))
            ->assertOk()
            ->assertSee('TKD-000007')
            ->assertSee('250.00');
    }

    public function test_rechaza_un_token_invalido(): void
    {
        $payload = $this->payload(['token' => 'token-falso']);

        $this->get('/pagar?'.http_build_query($payload))
            ->assertForbidden();
    }

    public function test_confirma_el_pago_en_el_modulo_pos_y_regresa_a_la_compra(): void
    {
        Http::fake([
            'http://pos.test/api/ventas/7/pagar' => Http::response([
                'estado' => 'pagada',
                'metodo_pago' => 'tarjeta',
            ], 200),
        ]);

        $payload = $this->payload();

        $this->post('/pagar', [
            ...$payload,
            'metodo_pago' => 'tarjeta',
            'titular' => 'Ana Lopez',
            'numero' => '4242424242424242',
            'vencimiento' => '12/29',
            'cvv' => '123',
        ])->assertRedirect($payload['return_url']);

        Http::assertSent(function ($request) use ($payload) {
            return $request->url() === 'http://pos.test/api/ventas/7/pagar'
                && $request['token'] === $payload['token']
                && $request['metodo_pago'] === 'tarjeta';
        });
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        $ventaId = 7;
        $total = '250.00';
        $token = hash_hmac('sha256', $ventaId.'|'.$total, 'test-secret');

        return array_merge([
            'venta_id' => $ventaId,
            'referencia' => 'TKD-000007',
            'total' => $total,
            'token' => $token,
            'return_url' => 'http://pos.test/compra/7',
        ], $overrides);
    }
}
