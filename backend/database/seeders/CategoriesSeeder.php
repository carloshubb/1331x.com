<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // Main Categories
            ['name' => 'Movies', 'slug' => 'movies', 'icon' => 'fas fa-film', 'color' => '#e74c3c', 'sort_order' => 1],
            ['name' => 'TV Shows', 'slug' => 'tv', 'icon' => 'fas fa-tv', 'color' => '#3498db', 'sort_order' => 2],
            ['name' => 'Games', 'slug' => 'games', 'icon' => 'fas fa-gamepad', 'color' => '#9b59b6', 'sort_order' => 3],
            ['name' => 'Music', 'slug' => 'music', 'icon' => 'fas fa-music', 'color' => '#f39c12', 'sort_order' => 4],
            ['name' => 'Apps', 'slug' => 'applications', 'icon' => 'fas fa-mobile-alt', 'color' => '#2ecc71', 'sort_order' => 5],
            ['name' => 'Anime', 'slug' => 'anime', 'icon' => 'fas fa-dragon', 'color' => '#e67e22', 'sort_order' => 6],
            ['name' => 'Documentaries', 'slug' => 'documentaries', 'icon' => 'fas fa-graduation-cap', 'color' => '#34495e', 'sort_order' => 7],
            ['name' => 'XXX', 'slug' => 'xxx', 'icon' => 'fas fa-heart', 'color' => '#e91e63', 'sort_order' => 8],
            ['name' => 'Other', 'slug' => 'other', 'icon' => 'fas fa-folder', 'color' => '#95a5a6', 'sort_order' => 9],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Sub-categories for Movies
        $moviesCategory = Category::where('slug', 'movies')->first();
        $movieSubCategories = [
            ['name' => 'HD Movies', 'slug' => 'movies-hd', 'parent_id' => $moviesCategory->id],
            ['name' => '4K Movies', 'slug' => 'movies-4k', 'parent_id' => $moviesCategory->id],
            ['name' => 'Bollywood', 'slug' => 'movies-bollywood', 'parent_id' => $moviesCategory->id],
            ['name' => 'Hollywood', 'slug' => 'movies-hollywood', 'parent_id' => $moviesCategory->id],
        ];

        foreach ($movieSubCategories as $subCat) {
            Category::create($subCat);
        }

        // Sub-categories for TV Shows
        $tvCategory = Category::where('slug', 'tv')->first();
        $tvSubCategories = [
            ['name' => 'TV Shows HD', 'slug' => 'tv-hd', 'parent_id' => $tvCategory->id],
            ['name' => 'TV Shows SD', 'slug' => 'tv-sd', 'parent_id' => $tvCategory->id],
        ];

        foreach ($tvSubCategories as $subCat) {
            Category::create($subCat);
        }

        // Sub-categories for Games
        $gamesCategory = Category::where('slug', 'games')->first();
        $gameSubCategories = [
            ['name' => 'PC Games', 'slug' => 'games-pc', 'parent_id' => $gamesCategory->id],
            ['name' => 'PlayStation', 'slug' => 'games-playstation', 'parent_id' => $gamesCategory->id],
            ['name' => 'Xbox', 'slug' => 'games-xbox', 'parent_id' => $gamesCategory->id],
            ['name' => 'Nintendo', 'slug' => 'games-nintendo', 'parent_id' => $gamesCategory->id],
            ['name' => 'Mobile Games', 'slug' => 'games-mobile', 'parent_id' => $gamesCategory->id],
        ];

        foreach ($gameSubCategories as $subCat) {
            Category::create($subCat);
        }
    }
}
