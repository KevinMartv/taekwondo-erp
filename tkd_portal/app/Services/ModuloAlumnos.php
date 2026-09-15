<?php

namespace App\Services;

/**
 * Cliente del módulo tkd_alumnos: dueño del expediente, los niveles,
 * los horarios y las fechas de asistencia.
 */
class ModuloAlumnos extends ClienteModulo
{
    protected function nombre(): string
    {
        return 'alumnos';
    }

    protected function baseUrl(): string
    {
        return (string) config('services.modules.alumnos.api');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function lista(): array
    {
        return $this->pedir('GET', '/alumnos');
    }

    /**
     * @return array<string, mixed>
     */
    public function ver(int $alumnoId): array
    {
        return $this->pedir('GET', "/alumnos/{$alumnoId}");
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function crear(array $datos): array
    {
        $respuesta = $this->pedir('POST', '/alumnos', $datos);

        return (array) ($respuesta['alumno'] ?? []);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function actualizar(int $alumnoId, array $datos): array
    {
        $respuesta = $this->pedir('PUT', "/alumnos/{$alumnoId}", $datos);

        return (array) ($respuesta['alumno'] ?? []);
    }

    public function cambiarEstado(int $alumnoId, bool $activo): string
    {
        $respuesta = $this->pedir('PATCH', "/alumnos/{$alumnoId}/estado", ['activo' => $activo]);

        return (string) ($respuesta['message'] ?? 'Estado actualizado.');
    }

    public function eliminar(int $alumnoId): string
    {
        $respuesta = $this->pedir('DELETE', "/alumnos/{$alumnoId}");

        return (string) ($respuesta['message'] ?? 'Alumno eliminado.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function niveles(): array
    {
        return $this->pedir('GET', '/niveles');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function horarios(): array
    {
        return $this->pedir('GET', '/horarios');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function asistencias(int $alumnoId): array
    {
        return $this->pedir('GET', "/alumnos/{$alumnoId}/asistencias");
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<string, mixed>
     */
    public function reservarAsistencia(int $alumnoId, array $datos): array
    {
        return $this->pedir('POST', "/alumnos/{$alumnoId}/asistencias", $datos);
    }

    public function cancelarAsistencia(int $alumnoId, int $asistenciaId): string
    {
        $respuesta = $this->pedir('DELETE', "/alumnos/{$alumnoId}/asistencias/{$asistenciaId}");

        return (string) ($respuesta['message'] ?? 'Asistencia cancelada.');
    }
}
