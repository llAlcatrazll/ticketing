<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Organization;
use App\Models\Subcategory;
use App\Models\Tag;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (User::query()->root()->doesntExist()) {
            User::factory()->root()->create();
        }

        $users = User::factory()->times(rand(5, 12));

        $tags = Tag::factory()->times(rand(1, 10));

        $categories = Category::factory()->times(rand(1, 5));

        $subcategories = Subcategory::factory()->times(rand(3, 9));

        Organization::factory(2)
            ->has($users)
            ->has($tags)
            ->has($categories->has($subcategories))
            ->create();
    }
}
