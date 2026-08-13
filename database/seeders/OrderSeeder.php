<?php

namespace Database\Seeders;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Prescription;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $khadija = User::where('email', 'khadija@example.com')->first();
        $sara = User::where('email', 'sara@example.com')->first();
 
        $panadol = Product::where('name', 'بانادول إكسترا')->first();
        $vitaminC = Product::where('name', 'فيتامين سي 1000 مغ')->first();
        $augmentin = Product::where('name', 'أوغمنتين 1 غ')->first();
        $rivotril = Product::where('name', 'ريفوتريل')->first();
 
        $delivery1 = Delivery::first();
        $delivery2 = Delivery::skip(1)->first();
 
        $acceptedPrescription = Prescription::where('status', 'approved')->firstOrFail();
        $pendingPrescription = Prescription::where('status', 'pending')->first();
 
        // سيناريو 1: طلب مكتمل بالكامل (بدون وصفة طبية)
        $order1 = Order::create([
            'user_id' => $khadija->id,
            'delivery_id' => $delivery1->id,
            'prescription_id' => null,
            'status' => 'delivered',
            'address' => 'دمشق - المزة - شارع الوادي',
            'assigned_at' => now()->subHours(3),
            'delivered_at' => now()->subHour(),
            'pharmacy_rating' => 5,
            'delivery_rating' => 4,
            'review_comments' => 'خدمة سريعة وممتازة',
        ]);
 
        $order1->orderItems()->createMany([
            ['product_id' => $panadol->id, 'quantity' => 2, 'price' => $panadol->price],
            ['product_id' => $vitaminC->id, 'quantity' => 1, 'price' => $vitaminC->price],
        ]);
 
        // سيناريو 2: طلب قيد التوصيل، فيه دواء يتطلب وصفة طبية مقبولة مسبقًا
        $order2 = Order::create([
            'user_id' => $sara->id,
            'delivery_id' => $delivery2->id,
            'prescription_id' => $acceptedPrescription->id,
            'status' => 'on_delivery',
            'address' => 'دمشق - أبو رمانة - شارع الجلاء',
            'assigned_at' => now()->subMinutes(30),
            'delivered_at' => null,
            'pharmacy_rating' => null,
            'delivery_rating' => null,
            'review_comments' => null,
        ]);
 
        $order2->orderItems()->create([
            'product_id' => $augmentin->id,
            'quantity' => 1,
            'price' => $augmentin->price,
        ]);
 
        // سيناريو 3: طلب قيد المراجعة، بانتظار قبول الوصفة الطبية، لسا ما تعيّن مندوب
        $order3 = Order::create([
            'user_id' => $khadija->id,
            'delivery_id' => null,
            'prescription_id' => $pendingPrescription->id,
            'status' => 'pending',
            'address' => 'دمشق - المزة - شارع الوادي',
            'assigned_at' => null,
            'delivered_at' => null,
            'pharmacy_rating' => null,
            'delivery_rating' => null,
            'review_comments' => null,
        ]);
 
        $order3->orderItems()->create([
            'product_id' => $rivotril->id,
            'quantity' => 1,
            'price' => $rivotril->price,
        ]);
    }
}
