<div x-data="{ open: false }" class="relative" @click.away="open = false">
    <!-- Cart Button -->
    <button @click="open = !open" 
            class="relative p-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white focus:outline-none transition">
        <!-- Cart Icon -->
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
            </path>
        </svg>
        
        <!-- Badge Count -->
        @if($cartCount > 0)
            <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-blue-600 rounded-full">
                {{ $cartCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="transform opacity-0 scale-95"
         x-transition:enter-end="transform opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="transform opacity-100 scale-100"
         x-transition:leave-end="transform opacity-0 scale-95"
         class="absolute right-0 mt-2 w-80 md:w-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl z-50 border border-gray-200 dark:border-gray-700"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ __('messages.your_cart') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ $cartCount }} {{ $cartCount === 1 ? __('messages.product') : __('messages.products') }}
            </p>
        </div>

        <!-- Cart Items -->
        <div class="max-h-96 overflow-y-auto">
            @if($cartItems->isEmpty())
                <div class="px-4 py-8 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z">
                        </path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">{{ __('messages.cart_empty') }}</p>
                </div>
            @else
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($cartItems as $item)
                        <div class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            <div class="flex items-start space-x-3">
                                <!-- Product Image -->
                                <div class="flex-shrink-0">
                                    @if($item->product->image)
                                        <img src="{{ $item->product->image }}" 
                                             alt="{{ $item->product->name }}"
                                             class="w-16 h-16 rounded object-cover">
                                    @else
                                        <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center">
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                                                </path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Info -->
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $item->product->name }}
                                    </h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        {{ __('messages.quantity') }}: {{ $item->quantity }} × ${{ number_format($item->product->price, 2) }}
                                    </p>
                                    <p class="text-sm font-semibold text-blue-600 dark:text-blue-400 mt-1">
                                        ${{ number_format($item->quantity * $item->product->price, 2) }}
                                    </p>
                                </div>

                                <!-- Remove Button -->
                                <button wire:click="removeItem('{{ $item->id }}')"
                                        class="flex-shrink-0 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition"
                                        title="{{ __('messages.remove') }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach

                    <!-- More items indicator -->
                    @if($cartCount > 5)
                        <div class="px-4 py-2 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('messages.and_more', ['count' => $cartCount - 5]) }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <!-- Footer -->
        @if(!$cartItems->isEmpty())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
                <!-- Total -->
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.total') }}:</span>
                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                        ${{ number_format($cartTotal, 2) }}
                    </span>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <a href="{{ route('cart') }}" 
                       wire:navigate
                       @click="open = false"
                       class="flex-1 text-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 font-medium transition">
                        {{ __('messages.view_cart') }}
                    </a>
                    <button wire:click="goToCheckout"
                            @click="open = false"
                            class="flex-1 text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        {{ __('messages.checkout') }}
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
