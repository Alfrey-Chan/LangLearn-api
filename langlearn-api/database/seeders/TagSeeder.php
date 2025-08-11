<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tag::create(['name' => 'daily-conversation']);
        Tag::create(['name' => 'business']);
        Tag::create(['name' => 'travel']);
        Tag::create(['name' => 'restaurant']);
        Tag::create(['name' => 'shopping']);
        Tag::create(['name' => 'social-media']);
        Tag::create(['name' => 'family']);
        Tag::create(['name' => 'dating']);
        Tag::create(['name' => 'school']);
        Tag::create(['name' => 'workplace']);
        Tag::create(['name' => 'healthcare']);
        Tag::create(['name' => 'transportation']);
        Tag::create(['name' => 'entertainment']);
        Tag::create(['name' => 'hobbies']);
    }
}
