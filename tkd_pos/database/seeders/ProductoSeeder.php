<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            [
                'nombre' => 'Dobok blanco entrenamiento',
                'categoria' => 'Uniforme',
                'descripcion' => 'Uniforme de taekwondo de algodón ligero, corte clásico para clases y competencia amateur.',
                'imagen_url' => '/images/productos/dobok.png',
                'precio' => 899.00,
                'stock' => 25,
            ],
            [
                'nombre' => 'Cinturón negro dan',
                'categoria' => 'Cinturones',
                'descripcion' => 'Cinturón negro de algodón grueso para grados dan, costura reforzada.',
                'imagen_url' => '/images/productos/cinturon-negro.png',
                'precio' => 249.00,
                'stock' => 18,
            ],
            [
                'nombre' => 'Pack cinturones de color',
                'categoria' => 'Cinturones',
                'descripcion' => 'Set de cinturones de grados de color para el dojang: blanco a rojo.',
                'imagen_url' => '/images/productos/cinturones.png',
                'precio' => 690.00,
                'stock' => 12,
            ],
            [
                'nombre' => 'Casco de combate WT',
                'categoria' => 'Protección',
                'descripcion' => 'Casco de sparring con protección de orejas, interior acolchado y cierre ajustable.',
                'imagen_url' => '/images/productos/casco.png',
                'precio' => 1190.00,
                'stock' => 10,
            ],
            [
                'nombre' => 'Peto / hogu de competencia',
                'categoria' => 'Protección',
                'descripcion' => 'Protector de torso reversible rojo-azul para combate olímpico.',
                'imagen_url' => '/images/productos/peto.png',
                'precio' => 1450.00,
                'stock' => 8,
            ],
            [
                'nombre' => 'Guantes de sparring',
                'categoria' => 'Protección',
                'descripcion' => 'Guantillas abiertas para combate, palma ventilada y cierre de velcro.',
                'imagen_url' => '/images/productos/guantes.png',
                'precio' => 320.00,
                'stock' => 30,
            ],
            [
                'nombre' => 'Espinilleras con empeine',
                'categoria' => 'Protección',
                'descripcion' => 'Par de espinilleras con protección de empeine, ideales para combate.',
                'imagen_url' => '/images/productos/espinilleras.png',
                'precio' => 410.00,
                'stock' => 22,
            ],
            [
                'nombre' => 'Protector bucal con estuche',
                'categoria' => 'Protección',
                'descripcion' => 'Protector bucal termomoldeable con estuche rígido de higiene.',
                'imagen_url' => '/images/productos/bucal.png',
                'precio' => 89.00,
                'stock' => 40,
            ],
            [
                'nombre' => 'Paleta doble de patadas',
                'categoria' => 'Entrenamiento',
                'descripcion' => 'Paleta de foco doble para precisión de patadas y combinaciones.',
                'imagen_url' => '/images/productos/paleta.png',
                'precio' => 275.00,
                'stock' => 16,
            ],
            [
                'nombre' => 'Escudo de impacto',
                'categoria' => 'Entrenamiento',
                'descripcion' => 'Escudo redondo de alta densidad para patadas de potencia.',
                'imagen_url' => '/images/productos/escudo.png',
                'precio' => 780.00,
                'stock' => 9,
            ],
            [
                'nombre' => 'Zapatos de taekwondo',
                'categoria' => 'Calzado',
                'descripcion' => 'Calzado ligero de suela flexible para tatami y piso de dojang.',
                'imagen_url' => '/images/productos/zapatos.png',
                'precio' => 560.00,
                'stock' => 14,
            ],
            [
                'nombre' => 'Mochila de equipo TKD',
                'categoria' => 'Accesorios',
                'descripcion' => 'Mochila resistente para dobok, protectores y accesorios de clase.',
                'imagen_url' => '/images/productos/mochila.png',
                'precio' => 640.00,
                'stock' => 15,
            ],
        ];

        foreach ($productos as $producto) {
            Producto::query()->updateOrCreate(
                ['nombre' => $producto['nombre']],
                [...$producto, 'activo' => true],
            );
        }
    }
}
