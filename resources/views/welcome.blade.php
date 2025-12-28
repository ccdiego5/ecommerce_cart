<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>E-commerce Cart - Inicio</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <a href="/" class="flex items-center space-x-2">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                            <span class="text-xl font-bold text-gray-900">E-commerce Cart</span>
                        </a>
                    </div>

                    <!-- Auth Links -->
                    <div class="flex items-center space-x-4">
                        <!-- Language Switcher -->
                        @livewire('language-switcher')
                        
                        @auth
                            <a href="{{ url('/dashboard') }}" class="text-gray-700 hover:text-blue-600 font-semibold transition">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 font-semibold transition">
                                {{ __('messages.login') }}
                            </a>
                            <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold transition">
                                {{ __('messages.create_account') }}
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            @livewire('landing-page')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">E-commerce Cart</h3>
                        <p class="text-gray-400">
                            Tu tienda online de confianza para encontrar los mejores productos.
                        </p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Enlaces Rápidos</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="{{ route('login') }}" class="hover:text-white transition">Iniciar Sesión</a></li>
                            <li><a href="{{ route('register') }}" class="hover:text-white transition">Registrarse</a></li>
                        </ul>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Contacto</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li>Email: info@ecommerce.test</li>
                            <li>Teléfono: +1 234 567 890</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; {{ date('Y') }} E-commerce Cart. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
