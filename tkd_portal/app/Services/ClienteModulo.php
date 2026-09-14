<?php

namespace App\Services;

use App\Exceptions\ModuloNoDisponible;
use App\Exceptions\OperacionRechazada;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

/**
 * Base de los clientes HTTP hacia los demás módulos del ERP.
 *
 * Traduce las respuestas del módulo a algo que el portal sabe mostrar:
 * un 422 se convierte en errores de validación del formulario y cualquier
 * otro fallo en ModuloNoDisponible.
 */
abstract class ClienteModulo
{
    abstract protected function nombre(): string;

    abstract protected function baseUrl(): string;

    protected function http(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl(), '/').'/api')
            ->acceptJson()
            ->asJson()
            ->timeout(10)
            ->connectTimeout(5);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>|array<int, mixed>
     */
    protected function pedir(string $metodo, string $ruta, array $datos = []): array
    {
        try {
            $respuesta = $this->http()->send($metodo, $ruta, $this->cuerpo($metodo, $datos));
        } catch (ConnectionException $e) {
            throw new ModuloNoDisponible($this->nombre(), 'Revisa que el servicio esté levantado.', $e);
        }

        return $this->interpretar($respuesta);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    private function cuerpo(string $metodo, array $datos): array
    {
        if (in_array(strtoupper($metodo), ['GET', 'HEAD', 'DELETE'], true)) {
            return ['query' => $datos];
        }

        return ['json' => $datos];
    }

    /**
     * @return array<string, mixed>|array<int, mixed>
     */
    private function interpretar(Response $respuesta): array
    {
        if ($respuesta->successful()) {
            return (array) $respuesta->json();
        }

        if ($respuesta->status() === 422) {
            throw ValidationException::withMessages(
                (array) ($respuesta->json('errors') ?: ['modulo' => $respuesta->json('message', 'Datos inválidos.')]),
            );
        }

        if ($respuesta->status() === 409) {
            throw new OperacionRechazada(
                (string) $respuesta->json('message', 'La operación fue rechazada por el módulo.'),
            );
        }

        throw new ModuloNoDisponible(
            $this->nombre(),
            'Respondió con el código '.$respuesta->status().'.',
        );
    }
}
