<?php

namespace Database\Seeders;

use App\Models\Banks;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Banks::create([
            'bank_name' => 'Bank of the Philippine Islands',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Metrobank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'BDO Unibank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Land Bank of the Philippines',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Philippine National Bank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Security Bank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'RCBC',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'EastWest Bank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Union Bank of the Philippines',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'China Bank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Philippine Bank of Communications',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Union Bank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'CitiBank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'HSBC',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Standard Chartered Bank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Bank of Commerce',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Asia United Bank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Security Bank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'Maybank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'RCBC Savings Bank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
        Banks::create([
            'bank_name' => 'BPI Family Savings Bank',
            'created_by'=> 1,
            'updated_by' => 1,
        ]);
    }
}
