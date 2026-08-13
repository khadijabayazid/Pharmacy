<?php

namespace Database\Seeders;

use App\Models\Prescription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Prescription::create([
            'image_path' => 'prescriptions/prescription1.jpg',
            'status' => 'approved', 
        ]);

        Prescription::create([
            'image_path' => 'prescriptions/prescription2.jpg',
            'status' => 'pending',
            'notes' => 'بانتظار مراجعة الصيدلي.'
        ]);
    }
}
