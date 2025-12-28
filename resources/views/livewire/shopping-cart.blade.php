<div>
    @if(!$showCheckoutForm)
        <!-- Vista del Carrito -->
        @if($cartItems->isEmpty())
            <!-- Empty Cart State -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                <svg class="w-24 h-24 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            <h3 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                {{ __('messages.cart_empty') }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                {{ __('messages.cart_empty_desc') }}
            </p>
            <a href="{{ route('dashboard') }}" 
               wire:navigate
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                {{ __('messages.view_products') }}
            </a>
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Cart Items -->
                <div class="lg:col-span-2 space-y-4">
                    @foreach($cartItems as $item)
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            <div class="flex items-start gap-4">
                                <!-- Product Image -->
                                <div class="flex-shrink-0">
                                    @if($item->product->image)
                                        <img src="{{ $item->product->image }}" 
                                             alt="{{ $item->product->name }}"
                                             class="w-24 h-24 rounded-lg object-cover">
                                    @else
                                        <div class="w-24 h-24 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                        {{ $item->product->name }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        {{ Str::limit($item->product->description, 100) }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('messages.stock_available') }}: {{ $item->product->stock_quantity }}
                                    </p>
                                    <p class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-2">
                                        ${{ number_format($item->product->price, 2) }}
                                    </p>
                                </div>

                                <!-- Quantity Controls -->
                                <div class="flex flex-col items-end gap-3">
                                    <button wire:click="removeItem('{{ $item->id }}')"
                                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition p-2"
                                            title="{{ __('messages.remove') }}">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>

                                    <div class="flex items-center gap-2">
                                        <button wire:click="updateQuantity('{{ $item->id }}', {{ max(1, $item->quantity - 1) }})"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                                            </svg>
                                        </button>

                                        <input type="number" 
                                               value="{{ $item->quantity }}"
                                               wire:change="updateQuantity('{{ $item->id }}', $event.target.value)"
                                               min="1"
                                               max="{{ $item->product->stock_quantity }}"
                                               class="w-16 text-center border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg">

                                        <button wire:click="updateQuantity('{{ $item->id }}', {{ $item->quantity + 1 }})"
                                                @if($item->quantity >= $item->product->stock_quantity) disabled @endif
                                                class="w-8 h-8 flex items-center justify-center bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        ${{ number_format($item->quantity * $item->product->price, 2) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 sticky top-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        {{ __('messages.order_summary') }}
                    </h3>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>{{ __('messages.subtotal') }} ({{ $cartItems->sum('quantity') }} {{ __('messages.products') }})</span>
                            <span>${{ number_format($total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>{{ __('messages.shipping') }}</span>
                            <span class="text-green-600 dark:text-green-400">{{ __('messages.free') }}</span>
                        </div>
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white">
                                <span>{{ __('messages.total') }}</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <button wire:click="proceedToCheckout"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-semibold transition duration-300 flex items-center justify-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>{{ __('messages.proceed_to_payment') }}</span>
                    </button>

                    <div class="mt-4 text-center">
                        <a href="{{ route('dashboard') }}" 
                           wire:navigate
                           class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                            {{ __('messages.continue_shopping') }}
                        </a>
                    </div>

                    <!-- Security Badges -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                <span>{{ __('messages.secure_payment_badge') }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>{{ __('messages.guaranteed') }}</span>
                            </div>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- Formulario de Checkout -->
        <div class="max-w-5xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Formulario -->
                <div class="lg:col-span-2">
                    <form wire:submit.prevent="checkout" class="space-y-6">
                        <!-- Información de Envío -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            <div class="flex items-center gap-2 mb-6">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ __('messages.shipping_information') }}
                                </h3>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.full_name') }} *
                                    </label>
                                    <input type="text" 
                                           wire:model="shipping_name"
                                           class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                           required>
                                    @error('shipping_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.phone') }} *
                                    </label>
                                    <input type="tel" 
                                           wire:model="shipping_phone"
                                           placeholder="+1 (555) 000-0000"
                                           class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                           required>
                                    @error('shipping_phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.country') }} *
                                    </label>
                                    <input type="text" 
                                           wire:model="shipping_country"
                                           class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                           required>
                                    @error('shipping_country') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.address') }} *
                                    </label>
                                    <input type="text" 
                                           wire:model="shipping_address"
                                           placeholder="{{ __('messages.address_placeholder') }}"
                                           class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                           required>
                                    @error('shipping_address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.city') }} *
                                    </label>
                                    <input type="text" 
                                           wire:model="shipping_city"
                                           class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                           required>
                                    @error('shipping_city') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.state') }} *
                                    </label>
                                    <input type="text" 
                                           wire:model="shipping_state"
                                           class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                           required>
                                    @error('shipping_state') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.zip_code') }} *
                                    </label>
                                    <input type="text" 
                                           wire:model="shipping_zip"
                                           class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                           required>
                                    @error('shipping_zip') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Información de Pago -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
                            <div class="flex items-center gap-2 mb-6">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ __('messages.payment_information') }}
                                </h3>
                            </div>

                            <!-- Tarjetas de Prueba -->
                            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                                <p class="text-sm text-blue-700 dark:text-blue-300 font-semibold mb-2">💳 {{ __('messages.test_cards') }}:</p>
                                <ul class="text-xs text-blue-600 dark:text-blue-400 space-y-1">
                                    <li><strong>Visa:</strong> 4532 1488 0343 6467</li>
                                    <li><strong>Mastercard:</strong> 5425 2334 3010 9903</li>
                                    <li><strong>Amex:</strong> 3782 822463 10005</li>
                                    <li class="mt-2 text-blue-500">CVV: 123 | Fecha: Cualquier fecha futura</li>
                                </ul>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.card_number') }} *
                                    </label>
                                    <input type="text" 
                                           wire:model="card_number"
                                           placeholder="1234 5678 9012 3456"
                                           maxlength="19"
                                           x-data
                                           x-on:input="$event.target.value = $event.target.value.replace(/\s/g, '').replace(/(.{4})/g, '$1 ').trim()"
                                           class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                           required>
                                    @error('card_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        {{ __('messages.name_on_card') }} *
                                    </label>
                                    <input type="text" 
                                           wire:model="card_name"
                                           placeholder="JOHN DOE"
                                           class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg uppercase"
                                           required>
                                    @error('card_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            {{ __('messages.expiration_date') }} *
                                        </label>
                                        <input type="text" 
                                               wire:model="card_expiry"
                                               placeholder="MM/YY"
                                               maxlength="5"
                                               x-data
                                               x-on:input="let v = $event.target.value.replace(/\D/g, ''); if(v.length >= 2) v = v.slice(0,2) + '/' + v.slice(2,4); $event.target.value = v;"
                                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                               required>
                                        @error('card_expiry') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            CVV *
                                        </label>
                                        <input type="text" 
                                               wire:model="card_cvv"
                                               placeholder="123"
                                               maxlength="3"
                                               pattern="\d{3}"
                                               class="w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg"
                                               required>
                                        @error('card_cvv') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="flex gap-4">
                            <button type="button"
                                    wire:click="backToCart"
                                    class="flex-1 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white py-3 px-4 rounded-lg font-semibold transition">
                                ← {{ __('messages.back_to_cart') }}
                            </button>
                            <button type="submit"
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 rounded-lg font-semibold transition flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                {{ __('messages.complete_payment') }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Resumen del Pedido -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 sticky top-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                            {{ __('messages.order_summary') }}
                        </h3>

                        <div class="space-y-3 mb-4">
                            @foreach($cartItems as $item)
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ $item->product->name }} x{{ $item->quantity }}
                                    </span>
                                    <span class="text-gray-900 dark:text-white font-semibold">
                                        ${{ number_format($item->quantity * $item->product->price, 2) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2">
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>{{ __('messages.subtotal') }}</span>
                                <span>${{ number_format($total, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-600 dark:text-gray-400">
                                <span>{{ __('messages.shipping') }}</span>
                                <span class="text-green-600 dark:text-green-400">{{ __('messages.free') }}</span>
                            </div>
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-2 mt-2">
                                <div class="flex justify-between text-lg font-bold text-gray-900 dark:text-white">
                                    <span>{{ __('messages.total') }}</span>
                                    <span>${{ number_format($total, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Security Badge -->
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                <span>{{ __('messages.secure_encrypted_payment') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Toast Notifications -->
    <div x-data="{ show: false, message: '', type: 'success' }"
         @show-toast.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 3000)"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-4 right-4 z-50"
         style="display: none;">
        <div :class="{
            'bg-green-500': type === 'success',
            'bg-red-500': type === 'error',
            'bg-blue-500': type === 'info'
        }" class="text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span x-text="message"></span>
        </div>
    </div>
</div>
