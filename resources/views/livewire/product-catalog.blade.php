<div>
    <!-- Search Bar -->
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" 
                       wire:model.live.debounce.300ms="search"
                       placeholder="{{ __('messages.search_products') }}"
                       class="flex-1 border-0 focus:ring-0 bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-400">
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-8">
        @forelse($products as $product)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition duration-300 overflow-hidden">
                <!-- Product Image -->
                <div class="aspect-square bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
                    @if($product->image)
                        <img src="{{ $product->image }}" 
                             alt="{{ $product->name }}"
                             class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-900 dark:to-blue-800">
                            <svg class="w-20 h-20 text-blue-300 dark:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    @endif
                    
                    <!-- Stock Badge -->
                    @if($product->stock_quantity > 0)
                        <span class="absolute top-2 right-2 bg-green-500 text-white text-xs font-semibold px-2 py-1 rounded">
                            {{ $product->stock_quantity }} {{ __('messages.available') }}
                        </span>
                    @else
                        <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded">
                            {{ __('messages.out_of_stock') }}
                        </span>
                    @endif

                    <!-- Low Stock Badge -->
                    @if($product->isLowStock() && $product->stock_quantity > 0)
                        <span class="absolute top-2 left-2 bg-yellow-500 text-white text-xs font-semibold px-2 py-1 rounded">
                            {{ __('messages.last_units') }}
                        </span>
                    @endif
                </div>

                <!-- Product Info -->
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2 min-h-[3rem]">
                        {{ $product->name }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm mb-3 line-clamp-2 min-h-[2.5rem]">
                        {{ Str::limit($product->description, 80) }}
                    </p>
                    
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                            ${{ number_format($product->price, 2) }}
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            ID: #{{ $product->formatted_public_id }}
                        </span>
                    </div>
                </div>

                <!-- Add to Cart Button -->
                <div class="p-4 pt-0">
                    @if($product->stock_quantity > 0)
                        <button wire:click="addToCart('{{ $product->id }}')"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg font-semibold transition duration-300 flex items-center justify-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span>{{ __('messages.add_to_cart') }}</span>
                        </button>
                    @else
                        <button disabled
                                class="w-full bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 py-2 px-4 rounded-lg font-semibold cursor-not-allowed">
                            {{ __('messages.out_of_stock') }}
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                    <svg class="w-20 h-20 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        {{ __('messages.no_products_found') }}
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        @if($search)
                            {{ __('messages.try_different_search') }}
                        @else
                            No hay productos disponibles en este momento
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
        <div class="mt-6">
            {{ $products->links() }}
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
