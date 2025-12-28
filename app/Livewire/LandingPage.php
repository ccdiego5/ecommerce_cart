<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;

class LandingPage extends Component
{
    public function render()
    {
        // Obtener 8 productos destacados (los que tienen más stock)
        $products = Product::where('is_active', true)
            ->orderBy('stock_quantity', 'desc')
            ->limit(8)
            ->get();

        return view('livewire.landing-page', [
            'products' => $products,
        ]);
    }
}
