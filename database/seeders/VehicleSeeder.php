<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = ['Vios', 'Wigo', 'Lancer', 'Fortuner'];
        $variants = ['1.5E', '1.0E', 'EX', '2.4G'];
        $colors = ['Silver', 'Red', 'Black', 'White'];

        foreach ($units as $unit) {
            foreach ($variants as $variant) {
                foreach ($colors as $color) {
                    Vehicle::create([
                        'unit' => $unit,
                        'variant' => $variant,
                        'color' => $color,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]);
                }
            }
        }
    }
}
