<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $zip_code = '';
    public string $country = 'United States';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'zip_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="register">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Name -->
            <div>
                <x-input-label for="name" :value="__('messages.full_name')" />
                <x-text-input wire:model="name" id="name" class="block mt-1 w-full" type="text" name="name" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input wire:model="email" id="email" class="block mt-1 w-full" type="email" name="email" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Phone -->
            <div>
                <x-input-label for="phone" :value="__('messages.phone')" />
                <x-text-input wire:model="phone" id="phone" class="block mt-1 w-full" type="tel" name="phone" required placeholder="+1 (555) 000-0000" autocomplete="tel" />
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>

            <!-- Country -->
            <div>
                <x-input-label for="country" :value="__('messages.country')" />
                <x-text-input wire:model="country" id="country" class="block mt-1 w-full" type="text" name="country" required autocomplete="country-name" />
                <x-input-error :messages="$errors->get('country')" class="mt-2" />
            </div>

            <!-- Address -->
            <div class="md:col-span-2">
                <x-input-label for="address" :value="__('messages.address')" />
                <x-text-input wire:model="address" id="address" class="block mt-1 w-full" type="text" name="address" required :placeholder="__('messages.address_placeholder')" autocomplete="street-address" />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>

            <!-- City -->
            <div>
                <x-input-label for="city" :value="__('messages.city')" />
                <x-text-input wire:model="city" id="city" class="block mt-1 w-full" type="text" name="city" required autocomplete="address-level2" />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>

            <!-- State -->
            <div>
                <x-input-label for="state" :value="__('messages.state')" />
                <x-text-input wire:model="state" id="state" class="block mt-1 w-full" type="text" name="state" required autocomplete="address-level1" />
                <x-input-error :messages="$errors->get('state')" class="mt-2" />
            </div>

            <!-- Zip Code -->
            <div class="md:col-span-2">
                <x-input-label for="zip_code" :value="__('messages.zip_code')" />
                <x-text-input wire:model="zip_code" id="zip_code" class="block mt-1 w-full" type="text" name="zip_code" required autocomplete="postal-code" />
                <x-input-error :messages="$errors->get('zip_code')" class="mt-2" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Password')" />
                <x-text-input wire:model="password" id="password" class="block mt-1 w-full"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div>
                <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block mt-1 w-full"
                                type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
