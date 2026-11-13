<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InquiryTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\InquryType::insert([
            ['inquiry_type' => 'Individual'],
            ['inquiry_type' => 'Fleet'],
            ['inquiry_type' => 'Government'],
            ['inquiry_type' => 'Company'],
        ]);
    }
}
