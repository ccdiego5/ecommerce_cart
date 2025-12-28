<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('messages.order_confirmation') }}
            </h2>
            <a href="{{ route('dashboard') }}" 
               wire:navigate
               class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                ← {{ __('messages.back_to_catalog') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:order-confirmation :orderId="$orderId" />
        </div>
    </div>
</x-app-layout>

