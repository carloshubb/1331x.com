<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    public function run()
    {
        if (!User::where('username', 'admin')->exists()) {
            User::create([
                'username' => 'admin',
                'email' => 'admin@torrentsite.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        }

        if (!User::where('username', 'moderator')->exists()) {
            User::create([
                'username' => 'moderator',
                'email' => 'mod@torrentsite.com',
                'password' => Hash::make('password'),
                'role' => 'moderator',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
        }

        // Create VIP users only if they don't exist
        for ($i = 1; $i <= 5; $i++) {
            $username = "vip_user_{$i}";
            if (!User::where('username', $username)->exists()) {
                User::create([
                    'username' => $username,
                    'email' => "vip{$i}@torrentsite.com",
                    'password' => Hash::make('password'),
                    'role' => 'vip',
                    'status' => 'active',
                    'upload_count' => rand(50, 200),
                    'download_count' => rand(100, 500),
                    'ratio' => rand(150, 300) / 100,
                    'email_verified_at' => now(),
                ]);
            }
        }

        // This will still create 100 new random users
        User::factory(100)->create();
    }
}
