<?php

namespace App\Livewire;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ShoppingCart extends Component
{
    public $cartItems = [];
    public $total = 0;
    public $showCheckoutForm = false;
    
    // Checkout form fields
    public $shipping_name = '';
    public $shipping_phone = '';
    public $shipping_address = '';
    public $shipping_city = '';
    public $shipping_state = '';
    public $shipping_zip = '';
    public $shipping_country = 'United States';
    
    public $card_number = '';
    public $card_expiry = '';
    public $card_cvv = '';
    public $card_name = '';

    protected $listeners = ['cart-updated' => 'loadCart'];

    public function mount()
    {
        $this->loadCart();
        
        // Pre-llenar con datos del usuario si existen
        $user = Auth::user();
        $this->shipping_name = $user->name;
        $this->shipping_phone = $user->phone ?? '';
        $this->shipping_address = $user->address ?? '';
        $this->shipping_city = $user->city ?? '';
        $this->shipping_state = $user->state ?? '';
        $this->shipping_zip = $user->zip_code ?? '';
        $this->shipping_country = $user->country ?? 'United States';
    }

    public function loadCart()
    {
        $this->cartItems = CartItem::with('product')
            ->where('user_id', Auth::id())
            ->get();

        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total = $this->cartItems->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
    }

    public function updateQuantity($cartItemId, $quantity)
    {
        if ($quantity < 1) {
            $this->removeItem($cartItemId);
            return;
        }

        // Verificar que el item pertenezca al usuario actual (SEGURIDAD)
        $cartItem = CartItem::where('id', $cartItemId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Verificar stock disponible
        if ($quantity > $cartItem->product->stock_quantity) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => __('messages.insufficient_stock')
            ]);
            return;
        }

        $cartItem->update(['quantity' => $quantity]);
        $this->loadCart();

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => __('messages.quantity_updated')
        ]);

        $this->dispatch('cart-updated');
    }

    public function removeItem($cartItemId)
    {
        // Verificar que el item pertenezca al usuario actual (SEGURIDAD)
        CartItem::where('id', $cartItemId)
            ->where('user_id', Auth::id())
            ->firstOrFail()
            ->delete();
            
        $this->loadCart();

        $this->dispatch('show-toast', [
            'type' => 'success',
            'message' => __('messages.product_removed')
        ]);

        $this->dispatch('cart-updated');
    }

    public function proceedToCheckout()
    {
        if ($this->cartItems->isEmpty()) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => __('messages.cart_empty_error')
            ]);
            return;
        }
        
        $this->showCheckoutForm = true;
    }
    
    public function backToCart()
    {
        $this->showCheckoutForm = false;
    }

    public function checkout()
    {
        // Validar datos del formulario
        $validated = $this->validate([
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_address' => 'required|string|max:255',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_zip' => 'required|string|max:20',
            'shipping_country' => 'required|string|max:100',
            'card_number' => 'required|string|size:19', // 16 dígitos + 3 espacios
            'card_expiry' => 'required|string|size:5', // MM/YY
            'card_cvv' => 'required|string|size:3',
            'card_name' => 'required|string|max:255',
        ], [
            'shipping_name.required' => 'El nombre es requerido',
            'shipping_phone.required' => 'El teléfono es requerido',
            'shipping_address.required' => 'La dirección es requerida',
            'shipping_city.required' => 'La ciudad es requerida',
            'shipping_state.required' => 'El estado es requerido',
            'shipping_zip.required' => 'El código postal es requerido',
            'card_number.required' => 'El número de tarjeta es requerido',
            'card_number.size' => 'Número de tarjeta inválido',
            'card_expiry.required' => 'La fecha de expiración es requerida',
            'card_expiry.size' => 'Formato de fecha inválido (MM/YY)',
            'card_cvv.required' => 'El CVV es requerido',
            'card_cvv.size' => 'El CVV debe tener 3 dígitos',
            'card_name.required' => 'El nombre en la tarjeta es requerido',
        ]);

        if ($this->cartItems->isEmpty()) {
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => __('messages.cart_empty_error')
            ]);
            return;
        }

        DB::beginTransaction();

        try {
            // Detectar tipo de tarjeta
            $cardBrand = $this->detectCardBrand($this->card_number);
            $cardLastFour = substr(str_replace(' ', '', $this->card_number), -4);
            
            // Crear la orden
            $order = Order::create([
                'user_id' => Auth::id(),
                'order_number' => Order::generateOrderNumber(),
                'total_amount' => $this->total,
                'status' => 'completed',
                'completed_at' => now(),
                'shipping_name' => $this->shipping_name,
                'shipping_phone' => $this->shipping_phone,
                'shipping_address' => $this->shipping_address,
                'shipping_city' => $this->shipping_city,
                'shipping_state' => $this->shipping_state,
                'shipping_zip' => $this->shipping_zip,
                'shipping_country' => $this->shipping_country,
                'payment_method' => 'credit_card',
                'card_last_four' => $cardLastFour,
                'card_brand' => $cardBrand,
            ]);

            // Crear los items de la orden y reducir stock
            foreach ($this->cartItems as $cartItem) {
                $product = $cartItem->product;

                // Verificar stock disponible
                if ($product->stock_quantity < $cartItem->quantity) {
                    throw new \Exception("Stock insuficiente para {$product->name}");
                }

                // Crear order item
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->quantity * $product->price,
                ]);

                // Reducir stock
                $product->decrement('stock_quantity', $cartItem->quantity);

                // TODO: Disparar Job de Low Stock si es necesario
                if ($product->isLowStock()) {
                    // LowStockNotificationJob::dispatch($product);
                }
            }

            // Actualizar dirección del usuario si cambió
            Auth::user()->update([
                'phone' => $this->shipping_phone,
                'address' => $this->shipping_address,
                'city' => $this->shipping_city,
                'state' => $this->shipping_state,
                'zip_code' => $this->shipping_zip,
                'country' => $this->shipping_country,
            ]);

            // Vaciar el carrito
            CartItem::where('user_id', Auth::id())->delete();

            DB::commit();

            // Redirigir a página de confirmación
            return $this->redirect(route('order.confirmation', ['orderId' => $order->id]), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();

            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => __('messages.processing_error') . ': ' . $e->getMessage()
            ]);
        }
    }
    
    protected function detectCardBrand($cardNumber)
    {
        $cardNumber = str_replace(' ', '', $cardNumber);
        
        // Patrones de tarjetas comunes
        if (preg_match('/^4/', $cardNumber)) {
            return 'Visa';
        } elseif (preg_match('/^5[1-5]/', $cardNumber)) {
            return 'Mastercard';
        } elseif (preg_match('/^3[47]/', $cardNumber)) {
            return 'American Express';
        } elseif (preg_match('/^6(?:011|5)/', $cardNumber)) {
            return 'Discover';
        }
        
        return 'Unknown';
    }

    public function render()
    {
        return view('livewire.shopping-cart');
    }
}
