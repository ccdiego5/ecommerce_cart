<?php

namespace App\Livewire;

use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class CartDropdown extends Component
{
    public $cartItems = [];
    public $cartCount = 0;
    public $cartTotal = 0;

    protected $listeners = ['cart-updated' => 'loadCart'];

    public function mount()
    {
        $this->loadCart();
    }

    public function loadCart()
    {
        $this->cartItems = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->latest()
            ->limit(5) // Solo mostrar los últimos 5 en el dropdown
            ->get();

        $allItems = CartItem::where('user_id', Auth::id())->get();
        $this->cartCount = $allItems->sum('quantity');
        
        $this->cartTotal = $allItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
    }

    public function removeItem($cartItemId)
    {
        try {
            Log::info('Intentando eliminar item del carrito', ['cart_item_id' => $cartItemId, 'user_id' => Auth::id()]);
            
            $cartItem = CartItem::find($cartItemId);
            
            if (!$cartItem) {
                Log::warning('Item no encontrado', ['cart_item_id' => $cartItemId]);
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'Producto no encontrado en el carrito'
                ]);
                return;
            }
            
            // Verificar que el item pertenezca al usuario actual
            if ($cartItem->user_id !== Auth::id()) {
                Log::warning('Intento de eliminar item de otro usuario', ['cart_item_id' => $cartItemId]);
                $this->dispatch('show-toast', [
                    'type' => 'error',
                    'message' => 'No autorizado'
                ]);
                return;
            }
            
            $cartItem->delete();
            Log::info('Item eliminado exitosamente', ['cart_item_id' => $cartItemId]);
            
            $this->loadCart();

            $this->dispatch('cart-updated');
            
            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Producto eliminado del carrito'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al eliminar item del carrito', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }

    public function goToCheckout()
    {
        return $this->redirect(route('cart'), navigate: true);
    }

    public function render()
    {
        return view('livewire.cart-dropdown');
    }
}
