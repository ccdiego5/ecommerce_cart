<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles si no existen
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // Usuario Admin (Diego - ROOT)
        $admin = User::create([
            'name' => 'Diego Cardenas',
            'email' => 'ccdiego.ve@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('Halo5585'),
            'is_admin' => true,
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Main Street, Apt 4B',
            'city' => 'New York',
            'state' => 'NY',
            'zip_code' => '10001',
            'country' => 'United States',
        ]);
        $admin->assignRole($adminRole);

        // Usuarios de prueba
        $testUsers = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+1 (555) 234-5678',
                'address' => '456 Oak Avenue',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'zip_code' => '90001',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '+1 (555) 345-6789',
                'address' => '789 Pine Road',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip_code' => '60601',
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob@example.com',
                'phone' => '+1 (555) 456-7890',
                'address' => '321 Elm Street',
                'city' => 'Houston',
                'state' => 'TX',
                'zip_code' => '77001',
            ],
            [
                'name' => 'Alice Williams',
                'email' => 'alice@example.com',
                'phone' => '+1 (555) 567-8901',
                'address' => '654 Maple Drive',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'zip_code' => '85001',
            ],
        ];

        foreach ($testUsers as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'is_admin' => false,
                'phone' => $userData['phone'],
                'address' => $userData['address'],
                'city' => $userData['city'],
                'state' => $userData['state'],
                'zip_code' => $userData['zip_code'],
                'country' => 'United States',
            ]);
            $user->assignRole($customerRole);
        }

        $this->command->info('✓ 5 usuarios creados (1 admin + 4 usuarios de prueba)');
        $this->command->info('  Admin: ccdiego.ve@gmail.com | Password: GodAleGO##85');
        $this->command->info('  Test users: john@example.com, jane@example.com, bob@example.com, alice@example.com | Password: password');
    }
}
