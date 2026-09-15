<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    // Este módulo no publica raíz: su interfaz vive en /alumnos y el resto es API.
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/alumnos')->assertStatus(200);
    }
}
