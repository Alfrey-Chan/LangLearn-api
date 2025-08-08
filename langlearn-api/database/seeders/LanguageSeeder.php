<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::create(['code' => 'ja', 'name' => 'Japanese', 'native_name' => '日本語', 'is_active' => true]);
        Language::create(['code' => 'en', 'name' => 'English', 'native_name' => 'English', 'is_active' => true]);
        Language::create(['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'is_active' => true]);
    }
}
