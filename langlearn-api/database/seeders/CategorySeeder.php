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
        $data = json_decode(file_get_contents(database_path('seeders/taxonomy.json')), true);

        foreach($data['categories'] as $categoryData) {
            $category = Category::create(['category' => $categoryData['name']]);
            $subCategoryIds = [];
            foreach($categoryData['subcategories'] as $sub) {
                $subcategory = Subcategory::create([
                    'category_id' => $category->id,
                    'subcategory' => $sub
                ]);
                $subCategoryIds[] = $subcategory->id;
            }
            
        }

        foreach($data['tags']['proficiency_level'] as $tag) {
                Tag::create([
                    'tag_category' => 'proficency_level',
                    'tag' => $tag
                ]);
            }

            foreach($data['tags']['formality_level'] as $tag) {
                Tag::create([
                    'tag_category' => 'formality_level',
                    'tag' => $tag
                ]);
            }

            foreach($data['tags']['word_types'] as $tag) {
                Tag::create([
                    'tag_category' => 'word_types',
                    'tag' => $tag
                ]);
            }

            // foreach($data['tags']['priority_level'] as $tag) {
            //     Tag::create([
            //         'tag_category' => 'priority_level',
            //         'tag' => $tag
            //     ]);
            // }

            foreach($data['tags']['communication_context'] as $tag) {
                Tag::create([
                    'tag_category' => 'communication_context',
                    'tag' => $tag
                ]);
            }

            foreach($data['tags']['learning_focus'] as $tag) {
                Tag::create([
                    'tag_category' => 'learning_focus',
                    'tag' => $tag
                ]);
            }
    }
}
