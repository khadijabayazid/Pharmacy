<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // أدمن (إدارة الصيدلية)
        User::create([
            'name' => 'أحمد الصيدلي',
            'email' => 'admin@pharmacy.com',
            'password' => 'password123',
            'phone' => '0999111222',
            'role' => 'admin',
        ]);
 
        // عملاء
        User::create([
            'name' => 'خديجة محمود',
            'email' => 'khadija@example.com',
            'password' => 'password123',
            'phone' => '0999333444',
            'role' => 'customer',
        ]);
 
        User::create([
            'name' => 'سارة يوسف',
            'email' => 'sara@example.com',
            'password' => 'password123',
            'phone' => '0999555666',
            'role' => 'customer',
        ]);
 
        // حسابات مندوبي التوصيل (كل حساب لاحقًا يرتبط بسجل Delivery واحد بـ DeliverySeeder)
        User::create([
            'name' => 'محمد الديلفري',
            'email' => 'delivery1@pharmacy.com',
            'password' => 'password123',
            'phone' => '0999777888',
            'role' => 'delivery',
        ]);
 
        User::create([
            'name' => 'علي مندوب',
            'email' => 'delivery2@pharmacy.com',
            'password' => 'password123',
            'phone' => '0999999000',
            'role' => 'delivery',
        ]);
    }
    
}
