<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    /**
     * Seed the vehicle inventory. Units are picked from the vehicles that
     * actually exist, so this keeps working if the catalogue size changes.
     */
    public function run(): void
    {
        $vehicleIds = DB::table('vehicle')->pluck('id')->all();

        if (empty($vehicleIds)) {
            $this->command->warn('InventorySeeder: no vehicles found. Run VehicleSeeder first. Skipping.');
            return;
        }

        for ($i = 0; $i < 80; $i++) {
            $invoiceDate = now()->subDays(rand(15, 300));

            DB::table('inventory')->insert([
                'year_model'          => rand(2025, 2026),
                'vehicle_id'          => $vehicleIds[array_rand($vehicleIds)],
                'CS_number'           => 'CS' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'actual_invoice_date' => $invoiceDate,
                'delivery_date'       => $invoiceDate->copy()->addDays(rand(3, 20)),
                'invoice_number'      => 'INV' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'age'                 => rand(1, 10),
                'status'              => 'Available',
                'CS_number_status'    => 'Available',
                'remarks'             => null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }
}
