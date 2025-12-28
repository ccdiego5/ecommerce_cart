<?php

namespace App\Livewire;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class OrderConfirmation extends Component
{
    public $order;
    public $orderItems;

    public function mount($orderId)
    {
        // Cargar la orden con sus items
        $this->order = Order::with(['items.product', 'user'])
            ->where('id', $orderId)
            ->where('user_id', Auth::id()) // Seguridad: solo ver tus propias órdenes
            ->firstOrFail();

        $this->orderItems = $this->order->items;
    }

    public function continueShopping()
    {
        return $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
    {
        return view('livewire.order-confirmation');
    }
}
