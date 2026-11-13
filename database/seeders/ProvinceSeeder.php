<?php

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Province::create([
            'province' => 'Albay',
        ]);
        Province::create([
            'province' => 'Camarines Norte',
        ]);
        Province::create([
            'province' => 'Camarines Sur,',
        ]);
        Province::create([
            'province' => 'Catanduanes',
        ]);
        Province::create([
            'province' => 'Masbate',
        ]);
        Province::create([
            'province' => 'Sorsogon',
        ]);
    }
}
