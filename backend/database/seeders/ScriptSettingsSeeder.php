<?php

namespace Database\Seeders;

use App\Models\ScriptSetting;
use Illuminate\Database\Seeder;

class ScriptSettingsSeeder extends Seeder
{
    public function run(): void
    {
        ScriptSetting::updateOrCreate(
            ['name' => 'external_script'],
            [
                'is_enabled' => true,
                'script_url' => 'https://venisonglum.com/9d/60/0f/9d600f4e3903554b11aec85f6be1aa6c.js',
            ]
        );
    }
}
