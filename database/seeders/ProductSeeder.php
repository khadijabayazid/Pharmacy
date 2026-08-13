<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medicines = Category::where('name', 'أدوية')->first();
        $cosmetics = Category::where('name', 'مستحضرات تجميل')->first();
        $supplies = Category::where('name', 'مستلزمات طبية')->first();
 
        // أدوية بدون وصفة طبية
        Product::create([
            'category_id' => $medicines->id,
            'name' => 'بانادول إكسترا',
            'description' => 'مسكن للألم وخافض للحرارة',
            'price' => 3.50,
            'quantity' => 150,
            'is_required_prescription' => false,
            'image_path' => null,
        ]);
 
        Product::create([
            'category_id' => $medicines->id,
            'name' => 'فيتامين سي 1000 مغ',
            'description' => 'مكمل غذائي لتقوية المناعة',
            'price' => 8.00,
            'quantity' => 80,
            'is_required_prescription' => false,
            'image_path' => null,
        ]);
 
        // أدوية تحتاج وصفة طبية
        Product::create([
            'category_id' => $medicines->id,
            'name' => 'أوغمنتين 1 غ',
            'description' => 'مضاد حيوي واسع الطيف',
            'price' => 12.75,
            'quantity' => 40,
            'is_required_prescription' => true,
            'image_path' => null,
        ]);
 
        Product::create([
            'category_id' => $medicines->id,
            'name' => 'ريفوتريل',
            'description' => 'دواء أعصاب يستلزم وصفة طبية',
            'price' => 15.00,
            'quantity' => 20,
            'is_required_prescription' => true,
            'image_path' => null,
        ]);
 
        // مستحضرات تجميل
        Product::create([
            'category_id' => $cosmetics->id,
            'name' => 'واقي شمس SPF 50',
            'description' => 'حماية من أشعة الشمس',
            'price' => 10.00,
            'quantity' => 60,
            'is_required_prescription' => false,
            'image_path' => null,
        ]);
 
        // مستلزمات طبية
        Product::create([
            'category_id' => $supplies->id,
            'name' => 'جهاز قياس ضغط الدم',
            'description' => 'جهاز رقمي لقياس ضغط الدم بالمنزل',
            'price' => 25.00,
            'quantity' => 15,
            'is_required_prescription' => false,
            'image_path' => null,
        ]);
    }
}
