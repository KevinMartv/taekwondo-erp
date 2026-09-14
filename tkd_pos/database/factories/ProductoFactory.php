<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Producto>
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->words(3, true),
            'categoria' => 'Uniforme',
            'descripcion' => fake()->sentence(),
            'imagen_url' => '/images/productos/dobok.png',
            'precio' => fake()->randomFloat(2, 100, 2000),
            'stock' => 20,
            'activo' => true,
        ];
    }
}
