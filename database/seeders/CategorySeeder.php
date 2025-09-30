<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        $path = database_path("/seeders/data/categories_and_tags.json");
        $data = json_decode(file_get_contents($path), true);

        foreach($data['categories'] as $categoryData) {
            Category::create([
                'category_en' => $categoryData['english'],
                'category_jp' => $categoryData['japanese']
            ]);
        }     
    }
}
