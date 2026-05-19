<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\BoardList;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (Article::count() === 0) {
            Article::factory(20)->create();
        }

        if (BoardList::count() === 0) {
            foreach (['To Do', 'In Progress', 'Done'] as $i => $name) {
                BoardList::create(['name' => $name, 'position' => $i]);
            }
        }
    }
}
