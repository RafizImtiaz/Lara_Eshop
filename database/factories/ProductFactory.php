<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $title = fake()->unique()->name();
        $slug = Str::slug($title);


        $subCategories = [20,21];
        $subCatRandKey = array_rand($subCategories);

        $brands = [14,15,16,17,18,19,20,21,22,23,24];
        $brandsRandKey = array_rand($brands);


        return [
            'title' => $title,
            'slug' => $slug,
            'category_id' => 58,
            'sub_category_id' => $subCategories[$subCatRandKey],
            'brand_id' => $brands[$brandsRandKey],
            'price' => rand(100,10000),
            'sku' => rand(1000,100000),
            'track_qty' => 'Yes',
            'qty' => 10,
            'is_featured' => 'Yes',
            'status' => 1
        ];
    }
}
