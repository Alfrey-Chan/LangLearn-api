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
        $path = database_path("/seeders/data/categories_and_tags.json");
        $data = json_decode(file_get_contents($path), true);

        foreach($data["tags"] as $tagData) {
            Tag::create([
                "tag_en" => $tagData["english"],
                "tag_jp" => $tagData["japanese"]
            ]);
        }
    }
}
