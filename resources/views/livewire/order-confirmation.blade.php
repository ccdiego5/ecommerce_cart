<div class="max-w-4xl mx-auto space-y-6">
    <!-- Mensaje de Éxito -->
    <div class="bg-green-50 dark:bg-green-900/20 border-2 border-green-500 rounded-lg p-6 text-center">
        <div class="flex justify-center mb-4">
            <svg class="w-16 h-16 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <h2 class="text-2xl font-bold text-green-700 dark:text-green-400 mb-2">
            ¡Compra Exitosa!
        </h2>
        <p class="text-green-600 dark:text-green-300">
            Tu orden ha sido procesada correctamente
        </p>
    </div>

    <!-- Información de la Orden -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Detalles de la Orden
        </h3>
        
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Número de Orden</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $order->order_number }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Fecha</p>
                <p class="font-semibold text-gray-900 dark:text-white">{{ $order->completed_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Estado</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    {{ ucfirst($order->status) }}
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Pagado</p>
                <p class="font-bold text-xl text-gray-900 dark:text-white">${{ number_format($order->total_amount, 2) }}</p>
            </div>
            @if($order->card_brand && $order->card_last_four)
            <div class="col-span-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">Método de Pago</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                    {{ $order->card_brand }} •••• {{ $order->card_last_four }}
                </p>
            </div>
            @endif
        </div>

        <!-- Dirección de Envío -->
        @if($order->shipping_address)
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Dirección de Envío
            </h4>
            <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
                <p class="font-semibold">{{ $order->shipping_name }}</p>
                <p>{{ $order->shipping_address }}</p>
                <p>{{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}</p>
                <p>{{ $order->shipping_country }}</p>
                <p class="flex items-center gap-1 mt-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    {{ $order->shipping_phone }}
                </p>
            </div>
        </div>
        @endif

        <!-- Items de la Orden -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-4">Productos Comprados</h4>
            <div class="space-y-4">
                @foreach($orderItems as $item)
                <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700 last:border-0">
                    <div class="flex items-center space-x-4 flex-1">
                        @if($item->product && $item->product->image)
                        <img src="{{ $item->product->image }}" 
                             alt="{{ $item->product_name }}"
                             class="w-16 h-16 object-cover rounded-lg">
                        @else
                        <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                        @endif
                        
                        <div class="flex-1">
                            <h5 class="font-medium text-gray-900 dark:text-white">{{ $item->product_name }}</h5>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Cantidad: {{ $item->quantity }} x ${{ number_format($item->product_price, 2) }}
                            </p>
                        </div>
                    </div>
                    
                    <div class="text-right">
                        <p class="font-semibold text-gray-900 dark:text-white">
                            ${{ number_format($item->subtotal, 2) }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Resumen -->
        <div class="border-t border-gray-200 dark:border-gray-700 mt-6 pt-6">
            <div class="flex justify-between items-center text-lg font-bold">
                <span class="text-gray-900 dark:text-white">Total:</span>
                <span class="text-gray-900 dark:text-white">${{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="flex gap-4">
        <button wire:click="continueShopping"
                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition">
            Continuar Comprando
        </button>
        <a href="{{ route('dashboard') }}" 
           wire:navigate
           class="flex-1 text-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-semibold py-3 px-6 rounded-lg transition">
            Ver Catálogo
        </a>
    </div>

    <!-- Información Adicional -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex">
            <svg class="w-5 h-5 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
            </svg>
            <div class="text-sm text-blue-700 dark:text-blue-300">
                <p class="font-semibold mb-1">¡Gracias por tu compra!</p>
                <p>Guarda tu número de orden <strong>{{ $order->order_number }}</strong> para futuras referencias.</p>
            </div>
        </div>
    </div>
</div>
