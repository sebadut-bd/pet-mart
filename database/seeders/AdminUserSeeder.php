<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'super_admin')->first();

        // 1. Admin Account
        User::firstOrCreate(
            ['phone' => '+8801700000000'],
            [
                'name'     => 'Super Admin',
                'username' => 'admin',
                'email'    => 'admin@petmart.com',
                'password' => Hash::make('password123'),
                'type'     => 1,
                'role_id'  => $adminRole?->id,
                'status'   => true,
                'is_verified' => true,
            ]
        );

        // 2. Verified Seller Account
        User::firstOrCreate(
            ['phone' => '+8801711111111'],
            [
                'name'        => 'Dhaka Pet Breeders',
                'username'    => 'seller_dhaka',
                'email'       => 'seller@petmart.com',
                'password'    => Hash::make('password123'),
                'type'        => 2,
                'status'      => true,
                'is_verified' => true,
                'bio'         => 'Certified pet breeder & seller in Dhanmondi, Dhaka.',
                'address'     => 'Road 27, Dhanmondi, Dhaka',
            ]
        );

        // 3. Regular Buyer Account
        User::firstOrCreate(
            ['phone' => '+8801722222222'],
            [
                'name'        => 'Rahim Ahmed',
                'username'    => 'buyer_rahim',
                'email'       => 'buyer@petmart.com',
                'password'    => Hash::make('password123'),
                'type'        => 3,
                'status'      => true,
                'is_verified' => false,
                'address'     => 'Uttara Sector 3, Dhaka',
            ]
        );
    }
}
