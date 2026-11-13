<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            UsertypeSeeder::class,
            TeamSeeder::class,
            ProvinceSeeder::class,
            VehicleSeeder::class,
            StatusSeeder::class,
            UserSeeder::class,
            BankSeeder::class,
            InquiryTypeSeeder::class,
            InventorySeeder::class,
            PermissionSeeder::class,
            PermissionUsertypeSeeder::class,

            // Sample CRM data (customers, inquiries, applications, transactions).
            // Must run last: it references the rows created above.
            CrmSampleSeeder::class,

        ]);


    }
}
