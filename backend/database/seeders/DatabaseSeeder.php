<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Database\Seeder;

/**
 * Datos de desarrollo (referencia: schema.sql).
 * Con contenido real esto lo carga el equipo desde Filament.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        /* ---- Equipo admin ---- */
        User::updateOrCreate(
            ['email' => 'admin@vang.test'],
            ['name' => 'Admin Vang', 'password' => 'password', 'role' => 'admin'],
        );

        User::updateOrCreate(
            ['email' => 'editor@vang.test'],
            ['name' => 'Editor Vang', 'password' => 'password', 'role' => 'editor'],
        );

        /* ---- Categorías ---- */
        $categories = [];
        foreach ([
            ['name' => 'Camisetas', 'slug' => 'camisetas', 'sort_order' => 1],
            ['name' => 'Hoodies', 'slug' => 'hoodies', 'sort_order' => 2],
            ['name' => 'Underwear', 'slug' => 'underwear', 'sort_order' => 3],
            ['name' => 'Accesorios', 'slug' => 'accesorios', 'sort_order' => 4],
        ] as $cat) {
            $categories[$cat['slug']] = Category::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        /* ---- Drops ---- */
        $drop01 = Collection::updateOrCreate(
            ['slug' => 'drop-01-origen'],
            ['name' => 'Drop 01 — Origen', 'published_at' => '2026-10-01 00:00:00'],
        );

        $drop02 = Collection::updateOrCreate(
            ['slug' => 'drop-02-noche-urbana'],
            [
                'name' => 'Drop 02 — Noche Urbana',
                // Fecha futura → "próximamente" con countdown
                'published_at' => '2026-12-15 00:00:00',
            ],
        );

        /* ---- Productos + variantes ---- */
        $products = [
            [
                'name' => 'Hoodie Oversize Graffiti',
                'slug' => 'hoodie-oversize-graffiti',
                'category' => 'hoodies', 'collection' => $drop01,
                'price' => 49.90, 'status' => 'published', 'featured' => true,
                'variants' => [
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'S', 'stock' => 5],
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'M', 'stock' => 0], // agotado → tachado en la web
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'L', 'stock' => 3],
                    ['color' => 'Beige', 'color_hex' => '#D9C7A7', 'size' => 'M', 'stock' => 2],
                ],
            ],
            [
                'name' => 'Camiseta Boxy Logo',
                'slug' => 'camiseta-boxy-logo',
                'category' => 'camisetas', 'collection' => $drop01,
                'price' => 24.90, 'status' => 'published', 'featured' => true,
                'variants' => [
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'S', 'stock' => 8],
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'M', 'stock' => 6],
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'L', 'stock' => 4],
                    ['color' => 'Blanco', 'color_hex' => '#F5F5F0', 'size' => 'M', 'stock' => 3],
                ],
            ],
            [
                'name' => 'Boxer Tricolor Band',
                'slug' => 'boxer-tricolor-band',
                'category' => 'underwear', 'collection' => $drop01,
                'price' => 14.90, 'status' => 'published', 'featured' => false,
                'variants' => [
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'M', 'stock' => 10],
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'L', 'stock' => 7],
                    ['color' => 'Blanco', 'color_hex' => '#F5F5F0', 'size' => 'M', 'stock' => 5],
                ],
            ],
            [
                'name' => 'Gorra 5-Panel Nocturna',
                'slug' => 'gorra-5-panel-nocturna',
                'category' => 'accesorios', 'collection' => $drop01,
                'price' => 19.90, 'status' => 'published', 'featured' => false,
                'variants' => [
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'ÚNICA', 'stock' => 12],
                ],
            ],
            [
                'name' => 'Camiseta Vintage Wash',
                'slug' => 'camiseta-vintage-wash',
                'category' => 'camisetas', 'collection' => $drop01,
                'price' => 27.90, 'status' => 'sold_out', 'featured' => false, // agotado completo
                'variants' => [
                    ['color' => 'Gris', 'color_hex' => '#8A8A8A', 'size' => 'M', 'stock' => 0],
                    ['color' => 'Gris', 'color_hex' => '#8A8A8A', 'size' => 'L', 'stock' => 0],
                ],
            ],
            [
                'name' => 'Pantalón Cargo Distopía',
                'slug' => 'pantalon-cargo-distopia',
                'category' => 'hoodies', 'collection' => $drop02,
                'price' => 59.90, 'status' => 'coming_soon', 'featured' => false, // se muestra con el countdown del drop
                'variants' => [
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'M', 'stock' => 15],
                    ['color' => 'Negro', 'color_hex' => '#111111', 'size' => 'L', 'stock' => 15],
                ],
            ],
        ];

        foreach ($products as $p) {
            $product = Product::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'name' => $p['name'],
                    'category_id' => $categories[$p['category']]->id,
                    'collection_id' => $p['collection']->id,
                    'price' => $p['price'],
                    'status' => $p['status'],
                    'featured' => $p['featured'],
                ],
            );

            foreach ($p['variants'] as $v) {
                Variant::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'color' => $v['color'],
                        'size' => $v['size'],
                    ],
                    [
                        'color_hex' => $v['color_hex'],
                        'stock' => $v['stock'],
                    ],
                );
            }
        }

        /* ---- Settings ---- */
        Setting::set('whatsapp_number', '34600000000');
        Setting::set('instagram_url', 'https://instagram.com/tumarca');
    }
}
