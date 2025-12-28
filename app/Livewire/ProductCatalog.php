<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\CartItem;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ProductCatalog extends Component
{
    use WithPagination;

    public $search = '';
    public $cartCount = 0;

    public function mount()
    {
        $this->updateCartCount();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function addToCart($productId)
    {
        try {
            Log::info('Intentando agregar producto al carrito', ['product_id' => $productId, 'user_id' => Auth::id()]);
            
            $product = Product::find($productId);
            
            if (!$product) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => __('messages.no_products_found')
                ]);
                return;
            }

            // Verificar si hay stock disponible
            if ($product->stock_quantity < 1) {
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => __('messages.out_of_stock')
                ]);
                return;
            }

            // Buscar si ya existe en el carrito
            $cartItem = CartItem::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                // Verificar si hay suficiente stock para aumentar la cantidad
                if ($cartItem->quantity >= $product->stock_quantity) {
                    $this->dispatch('show-toast', [
                        'type' => 'error',
                        'message' => __('messages.no_more_stock')
                    ]);
                    return;
                }

                // Incrementar cantidad
                $cartItem->increment('quantity');
                Log::info('Cantidad incrementada', ['cart_item_id' => $cartItem->id]);
            } else {
                // Crear nuevo item en el carrito
                $newItem = CartItem::create([
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                    'quantity' => 1,
                ]);
                Log::info('Nuevo item creado en carrito', ['cart_item_id' => $newItem->id]);
            }

            $this->updateCartCount();

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => __('messages.product_added')
            ]);

            // Notificar al componente del carrito que se actualizó
            $this->dispatch('cart-updated');
            
        } catch (\Exception $e) {
            Log::error('Error al agregar al carrito', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al agregar al carrito: ' . $e->getMessage()
            ]);
        }
    }

    public function updateCartCount()
    {
        $this->cartCount = CartItem::where('user_id', Auth::id())->sum('quantity');
    }

    public function render()
    {
        $products = Product::where('is_active', true)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'ilike', '%' . $this->search . '%')
                      ->orWhere('description', 'ilike', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('livewire.product-catalog', [
            'products' => $products
        ]);
    }
}
