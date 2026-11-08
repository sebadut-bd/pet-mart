<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $seller = User::where('phone', '+8801711111111')->first();
        if (!$seller) return;

        $dhakaDivision = Location::where('slug', 'dhaka')->first();
        $dhakaDistrict = Location::where('slug', 'dhaka-city')->first();
        $dhanmondiArea = Location::where('slug', 'dhanmondi')->first();
        $uttaraArea = Location::where('slug', 'uttara')->first() ?? $dhanmondiArea;
        $gulshanArea = Location::where('slug', 'gulshan')->first() ?? $dhanmondiArea;

        $catsCategory = Category::where('slug', 'cats')->first();
        $dogsCategory = Category::where('slug', 'dogs')->first();
        $birdsCategory = Category::where('slug', 'birds')->first();
        $fishCategory = Category::where('slug', 'fish-aquatics')->first();
        $suppliesCategory = Category::where('slug', 'pet-supplies')->first();

        // Listing 1: Persian Kitten
        if ($catsCategory) {
            $p1 = Product::firstOrCreate(
                ['slug' => 'healthy-white-doll-face-persian-kitten-2-months'],
                [
                    'user_id' => $seller->id,
                    'category_id' => $catsCategory->id,
                    'title' => 'Healthy White Doll-Face Persian Kitten (2 Months)',
                    'description' => 'Super playful and litter trained 2-month-old male Persian kitten. Dewormed, active, and very social. Comes with starter cat food pack and vaccination card.',
                    'price' => 18000.00,
                    'is_negotiable' => true,
                    'listing_type' => 'sell',
                    'division_id' => $dhakaDivision?->id,
                    'district_id' => $dhakaDistrict?->id,
                    'area_id' => $dhanmondiArea?->id,
                    'full_address' => 'House 45, Road 27, Dhanmondi, Dhaka',
                    'is_featured' => true,
                    'is_urgent' => false,
                    'views' => 142,
                    'status' => 'active',
                ]
            );

            ProductAttribute::firstOrCreate(['product_id' => $p1->id, 'attribute_key' => 'breed'], ['attribute_value' => 'Persian']);
            ProductAttribute::firstOrCreate(['product_id' => $p1->id, 'attribute_key' => 'age'], ['attribute_value' => '2 Months']);
            ProductAttribute::firstOrCreate(['product_id' => $p1->id, 'attribute_key' => 'gender'], ['attribute_value' => 'Male']);
            ProductAttribute::firstOrCreate(['product_id' => $p1->id, 'attribute_key' => 'vaccination_status'], ['attribute_value' => 'Partially Vaccinated']);

            ProductImage::firstOrCreate(['product_id' => $p1->id, 'image_path' => 'https://images.unsplash.com/photo-1514888286974-6c03e2ca1dba?w=800'], ['is_primary' => true, 'sort_order' => 0]);
        }

        // Listing 2: British Shorthair Kitten
        if ($catsCategory) {
            $p2 = Product::firstOrCreate(
                ['slug' => 'purebred-british-shorthair-blue-kitten'],
                [
                    'user_id' => $seller->id,
                    'category_id' => $catsCategory->id,
                    'title' => 'Purebred British Shorthair Blue Kitten',
                    'description' => 'Rare imported lineage British Shorthair kitten with thick plush coat and copper eyes. Fully health checked, litter box trained, and pot-trained.',
                    'price' => 35000.00,
                    'is_negotiable' => false,
                    'listing_type' => 'sell',
                    'division_id' => $dhakaDivision?->id,
                    'district_id' => $dhakaDistrict?->id,
                    'area_id' => $gulshanArea?->id,
                    'full_address' => 'Road 11, Gulshan 1, Dhaka',
                    'is_featured' => true,
                    'views' => 289,
                    'status' => 'active',
                ]
            );

            ProductAttribute::firstOrCreate(['product_id' => $p2->id, 'attribute_key' => 'breed'], ['attribute_value' => 'British Shorthair']);
            ProductImage::firstOrCreate(['product_id' => $p2->id, 'image_path' => 'https://images.unsplash.com/photo-1533738363-b7f9aef128ce?w=800'], ['is_primary' => true, 'sort_order' => 0]);
        }

        // Listing 3: German Shepherd Puppy
        if ($dogsCategory) {
            $p3 = Product::firstOrCreate(
                ['slug' => 'show-line-german-shepherd-puppy-3-months'],
                [
                    'user_id' => $seller->id,
                    'category_id' => $dogsCategory->id,
                    'title' => 'Show-Line German Shepherd Puppy (3 Months)',
                    'description' => 'Top quality show-line German Shepherd puppy with heavy bone structure, black & tan coat, and high drive. Fully vaccinated with vet health card.',
                    'price' => 32000.00,
                    'is_negotiable' => true,
                    'listing_type' => 'sell',
                    'division_id' => $dhakaDivision?->id,
                    'district_id' => $dhakaDistrict?->id,
                    'area_id' => $uttaraArea?->id,
                    'full_address' => 'Sector 4, Uttara, Dhaka',
                    'is_featured' => true,
                    'views' => 310,
                    'status' => 'active',
                ]
            );

            ProductAttribute::firstOrCreate(['product_id' => $p3->id, 'attribute_key' => 'breed'], ['attribute_value' => 'German Shepherd']);
            ProductImage::firstOrCreate(['product_id' => $p3->id, 'image_path' => 'https://images.unsplash.com/photo-1589941013453-ec89f33b5e95?w=800'], ['is_primary' => true, 'sort_order' => 0]);
        }

        // Listing 4: Free Kitten Adoption
        if ($catsCategory) {
            $p7 = Product::firstOrCreate(
                ['slug' => 'rescued-local-kitten-looking-loving-home-free-adoption'],
                [
                    'user_id' => $seller->id,
                    'category_id' => $catsCategory->id,
                    'title' => 'Rescued Local Kitten Looking for a Loving Home (Free Adoption)',
                    'description' => 'Rescued kitten, 3 months old, fully vet-checked, dewormed, and litter-trained. Looking for a compassionate pet lover to adopt him.',
                    'price' => 0,
                    'is_free' => true,
                    'listing_type' => 'adopt',
                    'division_id' => $dhakaDivision?->id,
                    'district_id' => $dhakaDistrict?->id,
                    'area_id' => $dhanmondiArea?->id,
                    'full_address' => 'Dhanmondi 32, Dhaka',
                    'is_featured' => true,
                    'views' => 450,
                    'status' => 'active',
                ]
            );

            ProductImage::firstOrCreate(['product_id' => $p7->id, 'image_path' => 'https://images.unsplash.com/photo-1561948955-570b270e7c36?w=800'], ['is_primary' => true, 'sort_order' => 0]);
        }
    }
}
