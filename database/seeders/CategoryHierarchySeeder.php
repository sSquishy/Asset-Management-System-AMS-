<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategoryHierarchySeeder extends Seeder
{
    public function run()
    {
        // Parents
        $deskside = Category::firstOrCreate([
            'name' => 'Deskside',
            'category_type' => 'asset',
        ]);

        $security = Category::firstOrCreate([
            'name' => 'Security Equipment',
            'category_type' => 'asset',
        ]);

        $patrol = Category::firstOrCreate([
            'name' => 'Patrol & Mobility',
            'category_type' => 'asset',
        ]);

        // Deskside children
        Category::firstOrCreate([
            'name' => 'Laptop',
            'category_type' => 'asset',
            'parent_id' => $deskside->id,
        ]);

        Category::firstOrCreate([
            'name' => 'Desktop',
            'category_type' => 'asset',
            'parent_id' => $deskside->id,
        ]);

        Category::firstOrCreate([
            'name' => 'Monitor',
            'category_type' => 'asset',
            'parent_id' => $deskside->id,
        ]);

        // Security children
        Category::firstOrCreate([
            'name' => 'Service Firearm',
            'category_type' => 'asset',
            'parent_id' => $security->id,
        ]);

        Category::firstOrCreate([
            'name' => 'Body Armor',
            'category_type' => 'asset',
            'parent_id' => $security->id,
        ]);

        // Patrol children
        Category::firstOrCreate([
            'name' => 'Patrol Vehicle',
            'category_type' => 'asset',
            'parent_id' => $patrol->id,
        ]);

        Category::firstOrCreate([
            'name' => 'Bicycle',
            'category_type' => 'asset',
            'parent_id' => $patrol->id,
        ]);
    }
}
