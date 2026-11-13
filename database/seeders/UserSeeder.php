<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use App\Models\Usertype;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        User::updateOrCreate(
            ['email' => 'crudph.dev@gmail.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'admin',
                'status' => 'Active',
                'usertype_id' => 1,
                'team_id' => null,
                'password' => Hash::make('qwerty...')
            ]
        );


    }
}
