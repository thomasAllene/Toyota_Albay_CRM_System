<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 60; $i++) {
            DB::table('inventory')->insert([
                'year_model' => rand(2023, 2024), // Random year between 2000 and 2024
                'vehicle_id' => rand(1, 60), // Assuming you have vehicle IDs from 1 to 100
                'CS_number' => 'CS' . str_pad($i + 1, 3, '0', STR_PAD_LEFT), // CS_number format
                'actual_invoice_date' => now()->subDays(rand(1, 365)), // Random date within the last year
                'delivery_date' => now()->subDays(rand(1, 365)), // Random date within the last year
                'invoice_number' => 'INV' . str_pad($i + 1, 5, '0', STR_PAD_LEFT), // Invoice number format
                'age' => rand(1, 10), // Random age between 1 and 10
                'status' => 'Available', // Default status
                'CS_number_status' => 'Available', // Default CS_number status
                'remarks' => 'Sample remark ' . ($i + 1), // Sample remarks
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
