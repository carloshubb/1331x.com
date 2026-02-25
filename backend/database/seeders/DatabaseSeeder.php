<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // no seeders called here
         $this->call([
            UsersSeeder::class,
            //CategoriesSeeder::class,
            ScriptSettingsSeeder::class,
        ]);
    }
}
