<?php

namespace Database\Seeders;

use App\Models\Usertype;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsertypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Usertype::create([
            'name' => 'SuperAdmin',
        ]);
        Usertype::create([
            'name' => 'General Manager',
        ]);
        Usertype::create([
            'name' => 'Group Manager',
        ]);
        // Usertype::create([
        //     'name' => 'Admin Staff',
        // ]);
        Usertype::create([
            'name' => 'Sales Admin Staff',
        ]);
        Usertype::create([
            'name' => 'Financing Staff',
        ]);
        Usertype::create([
            'name' => 'Agent',
        ]);
        Usertype::create([
            'name' => 'Vehicle Admin',
        ]);

    }
}
