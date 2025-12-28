<div>
    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-blue-600 to-blue-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-5xl md:text-6xl font-bold mb-6">
                    {{ __('messages.discover_products') }}
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-blue-100">
                    {{ __('messages.shop_description') }}
                </p>
                <a href="{{ route('login') }}" 
                   class="inline-block bg-white text-blue-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50 transition duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                    {{ __('messages.shop_now') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Featured Products Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                {{ __('messages.featured_products') }}
            </h2>
            <p class="text-lg text-gray-600">
                {{ __('messages.login_to_add_products') }}
            </p>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            @foreach($products as $product)
                <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition duration-300 overflow-hidden">
                    <!-- Product Image -->
                    <div class="aspect-square bg-gray-100 relative overflow-hidden">
                        @if($product->image)
                            <img src="{{ $product->image }}" 
                                 alt="{{ $product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-100 to-blue-200">
                                <svg class="w-20 h-20 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Stock Badge -->
                        @if($product->stock_quantity > 0)
                            <span class="absolute top-2 right-2 bg-green-500 text-white text-xs font-semibold px-2 py-1 rounded">
                                {{ __('messages.in_stock') }}
                            </span>
                        @else
                            <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded">
                                {{ __('messages.out_of_stock') }}
                            </span>
                        @endif
                    </div>

                    <!-- Product Info -->
                    <div class="p-4">
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">
                            {{ $product->name }}
                        </h3>
                        <p class="text-gray-600 text-sm mb-3 line-clamp-2">
                            {{ Str::limit($product->description, 80) }}
                        </p>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold text-blue-600">
                                ${{ number_format($product->price, 2) }}
                            </span>
                            <span class="text-sm text-gray-500">
                                Stock: {{ $product->stock_quantity }}
                            </span>
                        </div>
                    </div>

                    <!-- CTA Button -->
                    <div class="p-4 pt-0">
                        <a href="{{ route('login') }}" 
                           class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg font-semibold transition duration-300">
                            {{ __('messages.view_details') }}
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- CTA Section -->
        <div class="text-center bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-12">
            <h3 class="text-3xl font-bold text-gray-900 mb-4">
                {{ __('messages.ready_to_start') }}
            </h3>
            <p class="text-lg text-gray-600 mb-8">
                {{ __('messages.register_now_access') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('login') }}" 
                   class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition duration-300 shadow-lg hover:shadow-xl">
                    {{ __('messages.login') }}
                </a>
                <a href="{{ route('register') }}" 
                   class="inline-block bg-white hover:bg-gray-50 text-blue-600 border-2 border-blue-600 px-8 py-3 rounded-lg font-semibold transition duration-300">
                    {{ __('messages.create_account') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('messages.best_prices') }}</h3>
                    <p class="text-gray-600">{{ __('messages.best_prices_desc') }}</p>
                </div>

                <div class="text-center">
                    <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('messages.secure_shopping') }}</h3>
                    <p class="text-gray-600">{{ __('messages.secure_shopping_desc') }}</p>
                </div>

                <div class="text-center">
                    <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ __('messages.fast_delivery') }}</h3>
                    <p class="text-gray-600">{{ __('messages.fast_delivery_desc') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
