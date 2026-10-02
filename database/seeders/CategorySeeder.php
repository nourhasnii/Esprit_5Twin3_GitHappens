<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Fruits',
            'Légumes',
            'Huiles',
            'Produits laitiers',
            'Céréales',
            'Viandes',
            'Poissons',
            'Boissons',
            'Épices',
            'Autres',
        ] as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => true]
            );
        }

        $categories = Category::all()->keyBy(fn (Category $category) => mb_strtolower(trim($category->name)));

        Product::query()->whereNull('category_id')->chunkById(100, function ($products) use ($categories) {
            foreach ($products as $product) {
                $category = $categories->get(mb_strtolower(trim($product->category)));

                if ($category) {
                    $product->update(['category_id' => $category->id]);
                }
            }
        });
    }
}
