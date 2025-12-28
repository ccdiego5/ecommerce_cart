<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Obteniendo productos de DummyJSON API...');
        
        try {
            // Consumir API de DummyJSON
            $response = Http::get('https://dummyjson.com/products?limit=30');
            
            if (!$response->successful()) {
                $this->command->error('Error al obtener productos de la API');
                return;
            }
            
            $apiProducts = $response->json()['products'];
            
            $this->command->info('Procesando ' . count($apiProducts) . ' productos...');
            
            foreach ($apiProducts as $index => $apiProduct) {
                // Mapear campos de la API a nuestra estructura
                Product::create([
                    'name' => $apiProduct['title'],
                    'description' => $apiProduct['description'],
                    'price' => $apiProduct['price'],
                    'stock_quantity' => $apiProduct['stock'],
                    'image' => $apiProduct['thumbnail'] ?? $apiProduct['images'][0] ?? null,
                    // Variar el low_stock_threshold para tener productos con stock bajo
                    'low_stock_threshold' => $index % 3 === 0 ? $apiProduct['stock'] + 5 : 10,
                    'is_active' => true,
                ]);
            }
            
            $lowStockCount = Product::whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
            $normalStockCount = Product::whereColumn('stock_quantity', '>', 'low_stock_threshold')->count();
            
            $this->command->info('✓ ' . count($apiProducts) . ' productos creados desde DummyJSON API');
            $this->command->newLine();
            $this->command->info('📦 Productos con stock bajo: ' . $lowStockCount . ' (para probar Low Stock Job)');
            $this->command->info('📦 Productos con stock normal: ' . $normalStockCount);
            
        } catch (\Exception $e) {
            $this->command->error('Error al procesar productos: ' . $e->getMessage());
        }
    }
}
