<?php

namespace Database\Seeders;

use App\Models\Delivery;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeliverySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $delivery1 = User::where('email', 'delivery1@pharmacy.com')->first();
        $delivery2 = User::where('email', 'delivery2@pharmacy.com')->first();
 
        Delivery::create([
            'user_id' => $delivery1->id,
            'vehicle_type' => 'دراجة نارية',
            'vehicle_number' => 'DAM-1234',
            'is_available' => true,
        ]);
 
        Delivery::create([
            'user_id' => $delivery2->id,
            'vehicle_type' => 'سيارة',
            'vehicle_number' => 'DAM-5678',
            'is_available' => false,
        ]);
    }
}
