<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Generates a coherent set of sample CRM records for development / demo:
 * agents, customers, inquiries, applications and transactions.
 *
 * It inserts against the real table columns (via the query builder) rather
 * than Eloquent, because several models' $fillable arrays are out of sync
 * with the current schema. All foreign keys are resolved from rows that
 * already exist, so this seeder must run AFTER the supporting seeders
 * (Usertype, Status, InquiryType, Team, Vehicle, Bank, Inventory).
 */
class CrmSampleSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('en_PH');
        $now   = now();

        // ---- Resolve foreign keys from existing rows --------------------
        $vehicleIds   = DB::table('vehicle')->pluck('id')->all();
        $teamIds      = DB::table('team')->pluck('id')->all();
        $bankIds      = DB::table('banks')->pluck('id')->all();
        $inventoryIds = DB::table('inventory')->pluck('id')->all();
        $inquiryTypeIds = DB::table('inquiry_type')->pluck('id')->all();

        if (empty($vehicleIds)) {
            $this->command->warn('CrmSampleSeeder: no vehicles found. Enable VehicleSeeder first. Skipping.');
            return;
        }

        // Inquiry/application statuses that make sense for the sales flow.
        // (Status ids 1-9 are the transaction-side statuses from StatusSeeder.)
        $flowStatusIds = [1, 2, 3, 4, 5, 6, 7, 8, 9];

        // The dashboard treats a transaction as "released" only when its
        // reservation_transaction_status is Released or Posted, so resolve
        // those ids by name (don't hard-code).
        $releasedStatusId = DB::table('status')->where('status', 'Released')->value('id');
        $postedStatusId   = DB::table('status')->where('status', 'Posted')->value('id');
        $releaseStatusIds = array_values(array_filter([$releasedStatusId, $postedStatusId]));
        $pendingReleaseStatusId = DB::table('status')->where('status', 'Pending For Release')->value('id');

        // Sources must match the set the dashboard's "by source" chart looks
        // for, otherwise those rows are silently excluded from that chart.
        $sources       = ['Social-Media', 'Referral', 'Mall Duty', 'Show Room', 'Saturation', 'Repeat-Customer'];
        $transactions  = ['Cash', 'Financing'];
        $genders       = ['Male', 'Female'];

        // ---- A few sales agents so created_by / team vary ----------------
        $userIds = [1]; // seeded Super admin
        for ($i = 1; $i <= 6; $i++) {
            $id = DB::table('users')->insertGetId([
                'first_name'  => $faker->firstName,
                'last_name'   => $faker->lastName,
                'email'       => "agent{$i}@toyotaalbay.test",
                'status'      => 'Active',
                'usertype_id' => 6, // Agent
                'team_id'     => $teamIds ? $faker->randomElement($teamIds) : null,
                'password'    => Hash::make('password'),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $userIds[] = $id;
        }

        // Tag each inventory unit to an agent. Inventory::user() joins on the
        // `tag` column, and the vehicle release/reservation datatables render
        // $data->inventory->user, which would be null (and crash) otherwise.
        foreach ($inventoryIds as $invId) {
            DB::table('inventory')->where('id', $invId)->update([
                'tag'     => $faker->randomElement($userIds),
                'team_id' => $teamIds ? $faker->randomElement($teamIds) : null,
            ]);
        }

        // ---- Customers ---------------------------------------------------
        $customerIds = [];
        for ($i = 0; $i < 50; $i++) {
            $gender = $faker->randomElement($genders);
            $birth  = $faker->dateTimeBetween('-60 years', '-20 years');
            $by     = $faker->randomElement($userIds);

            $customerIds[] = DB::table('customer')->insertGetId([
                'inquiry_id'         => null,
                'inquiry_type_id'    => $faker->randomElement($inquiryTypeIds),
                'customer_first_name'=> $faker->firstName($gender === 'Male' ? 'male' : 'female'),
                'customer_last_name' => $faker->lastName,
                // Populate both so the Fleet/Company (company_name) and
                // Government (department_name) lead tabs always have a name
                // to show. The relevant tab decides which one is used.
                'department_name'    => $faker->randomElement([
                    'Office of the Governor', 'City Engineering Office',
                    'Provincial Health Office', 'DepEd Division Office',
                    'DPWH District Office', 'Municipal Treasury Office',
                ]),
                'company_name'       => $faker->company,
                'contact_number'     => '09' . $faker->numerify('#########'),
                'gender'             => $gender,
                'birthdate'          => $birth->format('Y-m-d'),
                'age'                => $now->year - (int) $birth->format('Y'),
                'source'             => $faker->randomElement($sources),
                'address'            => $faker->address,
                'created_by'         => $by,
                'updated_by'         => $by,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        // ---- Inquiries ---------------------------------------------------
        $inquiryIds = [];
        for ($i = 0; $i < 50; $i++) {
            $by         = $faker->randomElement($userIds);
            $customerId = $faker->randomElement($customerIds);
            $statusId   = $faker->randomElement($flowStatusIds);

            $inquiryIds[] = DB::table('inquiry')->insertGetId([
                'inquiry_type_id'   => $faker->randomElement($inquiryTypeIds),
                'customer_id'       => $customerId,
                'vehicle_id'        => $faker->randomElement($vehicleIds),
                'transaction'       => $faker->randomElement($transactions),
                'category'          => $faker->randomElement(['New', 'Used']),
                'quantity'          => $faker->numberBetween(1, 3),
                'remarks'           => $faker->optional(0.5)->sentence,
                'date'              => $faker->dateTimeBetween('-6 months', 'now')->format('F j'),
                'status_id'         => (string) $statusId,
                'notif_status'      => $faker->randomElement(['open', 'read']),
                'is_dispute'        => 0,
                'created_by'        => $by,
                'status_updated_by' => $by,
                'status_updated_at' => $now->copy()->subDays($faker->numberBetween(0, 30))->format('Y-m-d'),
                'updated_by'        => $by,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ---- Disputes (a second agent contests an existing lead) ---------
        // The Disputes datatable resolves the ORIGINAL (is_dispute=0) inquiry
        // for the same customer, so every dispute must have a non-disputed
        // sibling. We build each dispute from a real base inquiry, assigning a
        // DIFFERENT agent and a status other than "Processed" (which the
        // dispute query excludes).
        $disputeStatusIds = array_values(array_diff($flowStatusIds, [3])); // minus Processed
        $baseInquiries    = DB::table('inquiry')->where('is_dispute', 0)->inRandomOrder()->limit(6)->get();
        foreach ($baseInquiries as $base) {
            $others     = array_values(array_diff($userIds, [$base->created_by]));
            $otherAgent = $faker->randomElement($others ?: $userIds);

            DB::table('inquiry')->insert([
                'inquiry_type_id'   => $base->inquiry_type_id,
                'customer_id'       => $base->customer_id,
                'vehicle_id'        => $faker->randomElement($vehicleIds),
                'transaction'       => $faker->randomElement($transactions),
                'category'          => $faker->randomElement(['New', 'Used']),
                'quantity'          => 1,
                'remarks'           => 'Disputed: another agent is also handling this client.',
                'date'              => $now->format('F j'),
                'status_id'         => (string) $faker->randomElement($disputeStatusIds),
                'notif_status'      => 'open',
                'is_dispute'        => 1,
                'created_by'        => $otherAgent,
                'status_updated_by' => $otherAgent,
                'status_updated_at' => $now->format('Y-m-d'),
                'updated_by'        => $otherAgent,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // ---- Applications --------------------------------------------------
        // Transaction type drives the dashboard's "per transaction type" pie
        // (Financing / Cash / PO). Bank + terms only apply to financing.
        $applicationIds = [];
        for ($i = 0; $i < 50; $i++) {
            $by     = $faker->randomElement($userIds);
            $txType = $faker->randomElement(['Financing', 'Financing', 'Cash', 'PO']);
            $isFin  = $txType === 'Financing';

            $applicationIds[] = DB::table('application')->insertGetId([
                'customer_id'  => $faker->randomElement($customerIds),
                'vehicle_id'   => $faker->randomElement($vehicleIds),
                'status_id'    => $faker->randomElement($flowStatusIds),
                'bank_id'      => $isFin && $bankIds ? $faker->randomElement($bankIds) : null,
                'terms'        => $isFin ? $faker->randomElement(['12', '24', '36', '48', '60']) : null,
                'percentage'   => $isFin ? $faker->randomElement(['20', '30', '40', '50']) : null,
                'transaction'  => $txType,
                'remarks'      => $faker->optional(0.4)->sentence,
                'notif_status' => $faker->randomElement(['open', 'read']),
                'created_by'   => $by,
                'updated_by'   => $by,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        }

        // ---- Transactions (ties the flow together) -----------------------
        // The dashboard counts a transaction only when it is a released
        // reservation: reservation_id IS NOT NULL and reservation_transaction_
        // status is Released/Posted. The monthly bar chart buckets by
        // MONTH(updated_at), while the pie/gender/bank/source charts default
        // to the current month. So we make ~40 released reservations:
        //   - 15 dated in the current month (fills the default-month charts)
        //   - the rest spread one-per-month across the previous 11 months
        //     (fills every bar of the 12-month chart)
        // The remaining ~10 are "Pending For Release" reservations so the
        // Vehicle Releases > Pending tab has data too.
        //
        // NOTE: transactions.status is a FOREIGN KEY to status.id (the app
        // reads it via Status::where('id', $data->status)), so it must hold an
        // id, never a status name.
        for ($i = 0; $i < 50; $i++) {
            $isReleased       = $i < 40 && !empty($releaseStatusIds);
            $isPendingRelease = !$isReleased && $pendingReleaseStatusId;
            $isReservation    = $isReleased || $isPendingRelease;

            // Status id this transaction carries (both status and
            // reservation_transaction_status, as the app keeps them in sync).
            $statusId = $isReleased
                ? $faker->randomElement($releaseStatusIds)
                : ($isPendingRelease ? $pendingReleaseStatusId : $faker->randomElement($flowStatusIds));

            if (!$isReleased) {
                $monthOffset = $faker->numberBetween(0, 11);
            } elseif ($i < 15) {
                $monthOffset = 0;                       // current month
            } else {
                $monthOffset = (($i - 15) % 11) + 1;    // months 1..11 back
            }

            $updatedAt = $now->copy()
                ->subMonthsNoOverflow($monthOffset)
                ->day($faker->numberBetween(1, 28))
                ->setTime($faker->numberBetween(8, 17), $faker->numberBetween(0, 59));
            $appDate      = $updatedAt->copy()->subDays($faker->numberBetween(20, 120));
            $reservedDate = $updatedAt->copy()->subDays($faker->numberBetween(5, 15));

            DB::table('transactions')->insert([
                'folder_number'      => 1000 + $i,
                'inquiry_id'         => $faker->randomElement($inquiryIds),
                // 1:1 with applications so every application row has a linked
                // transaction. The application module derives type / client
                // name / reservation status from transactions->first(); with
                // no transaction those are null and freeze the datatable.
                'application_id'     => $applicationIds[$i] ?? $faker->randomElement($applicationIds),
                'reservation_id'     => $isReservation ? 5000 + $i : null,
                'reservation_transaction_status' => $isReservation ? $statusId : null,
                'reservation_status' => $isReservation ? 'reserved' : 'none',
                'inventory_id'       => $inventoryIds ? $faker->randomElement($inventoryIds) : null,
                'team_id'            => $teamIds ? $faker->randomElement($teamIds) : null,
                'profit'             => $faker->randomFloat(2, 10000, 80000),
                'other_profit'       => $faker->optional(0.5)->randomFloat(2, 1000, 15000),
                'insurance'          => $faker->optional(0.5)->randomElement(['Toyota Insure', 'Malayan', 'Prudential']),
                'application_transaction_date' => $appDate->format('Y-m-d'),
                'transaction_updated_date'     => $updatedAt->format('Y-m-d'),
                'reservation_date'   => $isReservation ? $reservedDate->format('Y-m-d') : null,
                'released_date'      => $isReleased ? $updatedAt->format('Y-m-d') : null,
                'released_remarks'   => $isReleased ? $faker->optional(0.5)->sentence : null,
                'status'             => $statusId, // FK to status.id

                'notif_status'       => $faker->randomElement(['open', 'read']),
                'created_at'         => $appDate,
                'updated_at'         => $updatedAt,
            ]);
        }

        // ---- Backlog / archive records -----------------------------------
        // These are standalone archive tables. The backlog models relate to
        // each other (transaction_backlogs -> inquiry/application/inventory
        // backlogs), so we build a self-consistent set rather than pointing
        // at the live rows above.

        // inventory_backlogs
        $invBacklogIds = [];
        for ($i = 0; $i < 20; $i++) {
            $by = $faker->randomElement($userIds);
            $invBacklogIds[] = DB::table('inventory_backlogs')->insertGetId([
                'year_model'       => $faker->numberBetween(2021, 2024),
                'vehicle_id'       => $faker->randomElement($vehicleIds),
                'CS_number'        => 'CSB' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'actual_invoice_date' => $faker->dateTimeBetween('-2 years', '-6 months')->format('Y-m-d'),
                'delivery_date'    => $faker->dateTimeBetween('-6 months', '-1 month')->format('Y-m-d'),
                'invoice_number'   => 'INVB' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'age'              => $faker->numberBetween(1, 24),
                'status'           => 'Released',
                'CS_number_status' => 'Released',
                'incoming_status'  => 'On Stock',
                'tag'              => $faker->randomElement($userIds), // InventoryBacklog::user() joins on tag
                'team_id'          => $teamIds ? $faker->randomElement($teamIds) : null,
                'remarks'          => $faker->optional(0.4)->sentence,
                'created_by'       => $by,
                'updated_by'       => $by,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }

        // inquiry_backlogs (no notif_status / soft deletes on this table)
        $inqBacklogIds = [];
        for ($i = 0; $i < 25; $i++) {
            $by = $faker->randomElement($userIds);
            $inqBacklogIds[] = DB::table('inquiry_backlogs')->insertGetId([
                'inquiry_type_id'   => $faker->randomElement($inquiryTypeIds),
                'customer_id'       => $faker->randomElement($customerIds),
                'vehicle_id'        => $faker->randomElement($vehicleIds),
                'transaction'       => $faker->randomElement($transactions),
                'category'          => $faker->randomElement(['New', 'Used']),
                'quantity'          => $faker->numberBetween(1, 3),
                'remarks'           => $faker->optional(0.5)->sentence,
                'date'              => $faker->dateTimeBetween('-18 months', '-6 months')->format('F j'),
                'status_id'         => (string) $faker->randomElement($flowStatusIds),
                'is_dispute'        => $faker->boolean(10) ? 1 : 0,
                'created_by'        => $by,
                'status_updated_by' => $by,
                'status_updated_at' => $faker->dateTimeBetween('-18 months', '-6 months')->format('Y-m-d'),
                'updated_by'        => $by,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        // application_backlogs (no notif_status on this table)
        $appBacklogIds = [];
        for ($i = 0; $i < 25; $i++) {
            $by = $faker->randomElement($userIds);
            $appBacklogIds[] = DB::table('application_backlogs')->insertGetId([
                'customer_id' => $faker->randomElement($customerIds),
                'vehicle_id'  => $faker->randomElement($vehicleIds),
                'status_id'   => $faker->randomElement($flowStatusIds),
                'bank_id'     => $bankIds ? $faker->randomElement($bankIds) : null,
                'terms'       => $faker->randomElement(['12', '24', '36', '48', '60']),
                'percentage'  => $faker->randomElement(['20', '30', '40', '50']),
                'transaction' => 'Financing',
                'remarks'     => $faker->optional(0.4)->sentence,
                'created_by'  => $by,
                'updated_by'  => $by,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        // transaction_backlogs (no insurance / other_profit on this table)
        // The Releases Backlogs module filters on reservation_transaction_status
        // IN (Released, Posted, Pending For Release) with reservation_id set,
        // and reads status as a FK to status.id (Status::where('id', ...)), so
        // these must be reservations carrying a status id, not a name.
        for ($i = 0; $i < 25; $i++) {
            $statusId = $faker->randomElement($releaseStatusIds ?: [$pendingReleaseStatusId]);
            $released = in_array($statusId, $releaseStatusIds, true); // Released/Posted => has a release date
            $appDate  = $faker->dateTimeBetween('-18 months', '-6 months');

            DB::table('transaction_backlogs')->insert([
                'folder_number'      => 2000 + $i,
                'inquiry_id'         => $faker->randomElement($inqBacklogIds),
                'application_id'     => $faker->randomElement($appBacklogIds),
                'reservation_id'     => 6000 + $i,
                'reservation_transaction_status' => $statusId,
                'reservation_status' => 'reserved',
                'inventory_id'       => $faker->randomElement($invBacklogIds),
                'team_id'            => $teamIds ? $faker->randomElement($teamIds) : null,
                'profit'             => $faker->randomFloat(2, 10000, 80000),
                'application_transaction_date' => $appDate->format('Y-m-d'),
                'transaction_updated_date'     => $faker->dateTimeBetween($appDate, '-6 months')->format('Y-m-d'),
                'reservation_date'   => $faker->dateTimeBetween($appDate, '-6 months')->format('Y-m-d'),
                'released_date'      => $released ? $faker->dateTimeBetween($appDate, '-6 months')->format('Y-m-d') : null,
                'released_remarks'   => $released ? $faker->optional(0.5)->sentence : null,
                'status'             => $statusId, // FK to status.id
                'lto_remarks'        => $faker->optional(0.3)->sentence,
                'notif_status'       => 'read',
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        $this->command->info('CrmSampleSeeder: created 6 agents, 50 customers, 50 inquiries, 6 disputes, 50 applications, 50 transactions.');
        $this->command->info('CrmSampleSeeder: created 20 inventory, 25 inquiry, 25 application, 25 transaction backlog records.');
    }
}
