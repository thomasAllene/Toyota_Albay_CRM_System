<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Seed the vehicle catalogue with a realistic Toyota Philippines line-up.
     * Each unit only gets the variants it is actually sold with.
     */
    public function run(): void
    {
        $lineup = [
            'Vios'     => ['1.3 XLE CVT', '1.5 G CVT'],
            'Wigo'     => ['1.0 E MT', '1.0 G CVT'],
            'Raize'    => ['1.2 E CVT', '1.0 Turbo CVT'],
            'Veloz'    => ['1.5 G CVT', '1.5 V CVT'],
            'Innova'   => ['2.8 E MT', '2.8 V AT'],
            'Fortuner' => ['2.4 G 4x2 AT', '2.8 LTD 4x4 AT'],
            'Hilux'    => ['2.4 E 4x2 MT', '2.8 Conquest 4x4 AT'],
            'Hiace'    => ['Commuter Deluxe', 'GL Grandia'],
        ];
        $colors = ['Silver', 'Red', 'Black', 'White'];

        foreach ($lineup as $unit => $variants) {
            foreach ($variants as $variant) {
                foreach ($colors as $color) {
                    Vehicle::create([
                        'unit'       => $unit,
                        'variant'    => $variant,
                        'color'      => $color,
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]);
                }
            }
        }
    }
}
