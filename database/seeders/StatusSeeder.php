<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Status::create(['status' => 'Pending']);
        Status::create(['status' => 'Approved']);
        Status::create(['status' => 'Processed']);
        Status::create(['status' => 'Cancel']);
        Status::create(['status' => 'Denied']);
        Status::create(['status' => 'Cash']);
        Status::create(['status' => 'Released']);
        Status::create(['status' => 'Pending For Release']);
        Status::create(['status' => 'Posted']);

        Status::create(['status' => 'Available']);
        Status::create(['status' => 'Invoice']);
        Status::create(['status' => 'Pull Out']);
        Status::create(['status' => 'In Transit']);
        Status::create(['status' => 'On Stock']);
        Status::create(['status' => 'Reserved']);
        Status::create(['status' => 'For Swapping']);
        Status::create(['status' => 'Freeze']);
        Status::create(['status' => 'Ear Mark']);

    }
}
