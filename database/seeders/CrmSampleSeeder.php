<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Generates a full, realistic year of demo CRM data for screenshots and the
 * public demo: sales agents, customers, leads, applications, reservations and
 * releases. Every name, number and address is made up.
 *
 * The data is spread across January up to today of the CURRENT year, because
 * the dashboard charts group by MONTH(updated_at) without a year filter.
 * Volumes grow through the year and each agent has a different performance
 * level, so the funnel, monthly charts and agent ranking all look like a
 * real dealership.
 *
 * It inserts against the real table columns (query builder, not Eloquent)
 * because several models' $fillable arrays are out of sync with the schema.
 * Must run AFTER Usertype, Team, Vehicle, Status, Bank, InquiryType and
 * Inventory seeders.
 */
class CrmSampleSeeder extends Seeder
{
    /** Made-up Filipino names, so the demo reads like a local dealership. */
    private array $maleFirst = [
        'Juan Carlo', 'Mark Anthony', 'John Paul', 'Rommel', 'Jerome', 'Ramil', 'Arnel', 'Christian',
        'Joseph', 'Rodel', 'Ryan', 'Jayson', 'Noel', 'Dennis', 'Edwin', 'Allan', 'Rey', 'Paolo',
        'Miguel', 'Carlo', 'Renato', 'Ernesto', 'Fernando', 'Joel', 'Marvin', 'Kenneth',
    ];

    private array $femaleFirst = [
        'Maria Clara', 'Kristine', 'Angelica', 'Jasmine', 'Rowena', 'Maricel', 'Liza', 'Sheila',
        'Catherine', 'Joanna', 'Michelle', 'Grace', 'Rhea', 'Mylene', 'Lorna', 'Erlinda',
        'Patricia', 'Camille', 'Bea', 'Andrea', 'Rosalie', 'Janine', 'Cristina', 'Mae',
    ];

    private array $lastNames = [
        'Reyes', 'Santos', 'Dela Cruz', 'Bautista', 'Villanueva', 'Mendoza', 'Garcia', 'Ramos',
        'Ocampo', 'Aquino', 'Castillo', 'Rivera', 'Navarro', 'Llorin', 'Ricafort', 'Sarmiento',
        'Bermas', 'Moral', 'Nuñez', 'Oñate', 'Bonavente', 'Arevalo', 'Belen', 'Cabrera',
        'Del Rosario', 'Esplana', 'Gonzales', 'Imperial', 'Jacob', 'Lim', 'Marcellana', 'Nieves',
        'Obias', 'Pesino', 'Quiñones', 'Rebueno', 'Sabater', 'Tan', 'Ubaldo', 'Velasco',
    ];

    private array $towns = [
        'Legazpi City', 'Tabaco City', 'Ligao City', 'Daraga', 'Camalig', 'Guinobatan',
        'Sto. Domingo', 'Malilipot', 'Bacacay', 'Polangui', 'Oas', 'Malinao', 'Tiwi', 'Manito',
    ];

    private array $barangays = [
        'Poblacion', 'San Roque', 'Bagumbayan', 'San Isidro', 'San Rafael', 'Rawis', 'Bonot',
        'Cabangan', 'San Vicente', 'Salvacion', 'Santa Cruz', 'Bigaa', 'Tagas', 'Matagbac',
    ];

    private array $companies = [
        'Mayon Agri Supply Corp.', 'Bicol Coastal Logistics Inc.', 'Albay Builders Corp.',
        'Cagsawa Food Products', 'Tabaco Bay Fishing Corp.', 'Kawa-Kawa Construction',
        'Daraga Rice Trading', 'Sorsogon Gateway Couriers', 'Ibalong Hardware Center',
        'Pili Nut Delights Inc.', 'Bicolano Water Refilling Co.', 'Embarcadero Hotel Group',
    ];

    private array $offices = [
        'Office of the Provincial Governor', 'City Engineering Office', 'Provincial Health Office',
        'DepEd Albay Division Office', 'DPWH Albay District Office', 'Municipal Treasury Office',
        'City Disaster Risk Reduction Office', 'Provincial Agriculture Office',
    ];

    public function run(): void
    {
        $faker = \Faker\Factory::create('en_PH');
        $faker->seed(2026);           // same data every run
        mt_srand(2026);

        $now  = now();
        $year = $now->year;

        // ---- Resolve foreign keys from existing rows --------------------
        $vehicleIds   = DB::table('vehicle')->pluck('id')->all();
        $teamIds      = DB::table('team')->pluck('id')->all();
        $bankIds      = DB::table('banks')->pluck('id')->all();
        $inventoryIds = DB::table('inventory')->pluck('id')->all();
        $types        = DB::table('inquiry_type')->pluck('id', 'inquiry_type')->all();
        $statusId     = DB::table('status')->pluck('id', 'status')->all();
        $agentTypeId  = DB::table('usertypes')->where('name', 'Agent')->value('id') ?? 6;

        if (empty($vehicleIds) || empty($statusId)) {
            $this->command->warn('CrmSampleSeeder: vehicles or statuses missing. Run the base seeders first. Skipping.');
            return;
        }

        $s = fn (string $name) => $statusId[$name] ?? null;

        // Lead (inquiry) statuses, weighted toward an active pipeline.
        $leadStatuses = array_values(array_filter([
            $s('Pending'), $s('Pending'), $s('Pending'), $s('Approved'), $s('Approved'),
            $s('Processed'), $s('Processed'), $s('Cancel'), $s('Denied'),
        ]));
        $inProcessStatuses = array_values(array_filter([$s('Pending'), $s('Approved'), $s('Processed')]));
        $releaseStatuses   = array_values(array_filter([$s('Released'), $s('Released'), $s('Posted')]));

        // Sources must match the dashboard's "by source" chart, otherwise
        // rows are silently left out of that chart.
        $sources = [
            'Show Room', 'Show Room', 'Show Room', 'Referral', 'Referral', 'Referral',
            'Social-Media', 'Social-Media', 'Social-Media', 'Social-Media',
            'Mall Duty', 'Mall Duty', 'Saturation', 'Repeat-Customer', 'Repeat-Customer',
        ];

        // Customer type mix: mostly walk-in individuals.
        $typeMix = array_values(array_filter([
            $types['Individual'] ?? null, $types['Individual'] ?? null, $types['Individual'] ?? null,
            $types['Individual'] ?? null, $types['Individual'] ?? null, $types['Individual'] ?? null,
            $types['Fleet'] ?? null, $types['Fleet'] ?? null,
            $types['Company'] ?? null, $types['Company'] ?? null,
            $types['Government'] ?? null, $types['Government'] ?? null,
        ])) ?: array_values($types);

        // Months Jan..current, with volume growing through the year.
        $months = range(1, $now->month);
        $growth = fn (int $m) => 0.6 + 0.6 * (($m - 1) / max(1, count($months) - 1)); // 0.6x -> 1.2x

        // Random date inside a month of this year, never in the future.
        $dateIn = function (int $month) use ($year, $now): Carbon {
            $maxDay = $month === $now->month
                ? max(1, $now->day)
                : Carbon::create($year, $month, 1)->daysInMonth;

            return Carbon::create($year, $month, mt_rand(1, $maxDay), mt_rand(8, 17), mt_rand(0, 59));
        };
        $capToNow = fn (Carbon $d) => $d->greaterThan($now) ? $now->copy() : $d;

        $phone = fn () => '09' . $faker->randomElement(['17', '18', '19', '20', '27', '28', '45', '55', '66', '77', '91', '95', '98'])
            . $faker->numerify('#######');

        // ---- Demo login with full access (for local runs and screenshots) ---
        DB::table('users')->insert([
            'first_name'  => 'Demo',
            'last_name'   => 'Manager',
            'email'       => 'demo@toyotaalbay.test',
            'status'      => 'Active',
            'usertype_id' => DB::table('usertypes')->where('name', 'SuperAdmin')->value('id') ?? 1,
            'team_id'     => null,
            'password'    => Hash::make('password'),
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        // ---- Sales agents --------------------------------------------------
        // Each agent gets a performance weight so the ranking has a clear spread.
        $agentNames = [
            ['Rommel', 'Bermas', 16], ['Kristine', 'Llorin', 14], ['Jerome', 'Ricafort', 13],
            ['Angelica', 'Sarmiento', 11], ['Paolo', 'Obias', 10], ['Maricel', 'Esplana', 9],
            ['Ramil', 'Nieves', 8], ['Jasmine', 'Cabrera', 7], ['Arnel', 'Belen', 6],
            ['Rhea', 'Marcellana', 5],
        ];

        $agents = [];       // id => weight
        $agentTeam = [];    // id => team id
        foreach ($agentNames as $i => [$first, $last, $weight]) {
            $team = $teamIds ? $teamIds[$i % count($teamIds)] : null;
            $id = DB::table('users')->insertGetId([
                'first_name'  => $first,
                'last_name'   => $last,
                'email'       => 'agent' . ($i + 1) . '@toyotaalbay.test',
                'status'      => 'Active',
                'usertype_id' => $agentTypeId,
                'team_id'     => $team,
                'password'    => Hash::make('password'),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $agents[$id]    = $weight;
            $agentTeam[$id] = $team;
        }
        $agentIds = array_keys($agents);

        $pickAgent = function () use ($agents): int {
            $roll = mt_rand(1, array_sum($agents));
            foreach ($agents as $id => $weight) {
                if (($roll -= $weight) <= 0) {
                    return $id;
                }
            }
            return array_key_first($agents);
        };

        // Tag inventory units to agents. Inventory::user() joins on `tag`, and
        // the reservation/release datatables crash when it is null.
        foreach ($inventoryIds as $invId) {
            $agent = $agentIds[array_rand($agentIds)];
            DB::table('inventory')->where('id', $invId)->update([
                'tag'     => $agent,
                'team_id' => $agentTeam[$agent],
            ]);
        }

        // ---- Customers ---------------------------------------------------
        $makeCustomer = function (int $agent, Carbon $when) use ($faker, $phone, $sources, $typeMix): int {
            $isMale = $faker->boolean(55);
            $birth  = $faker->dateTimeBetween('-62 years', '-22 years');
            $first  = $faker->randomElement($isMale ? $this->maleFirst : $this->femaleFirst);

            return DB::table('customer')->insertGetId([
                'inquiry_id'          => null,
                'inquiry_type_id'     => $faker->randomElement($typeMix),
                'customer_first_name' => $first,
                'customer_last_name'  => $faker->randomElement($this->lastNames),
                // Both are filled so the Fleet/Company and Government lead
                // tabs always have a name; the tab decides which one shows.
                'department_name'     => $faker->randomElement($this->offices),
                'company_name'        => $faker->randomElement($this->companies),
                'contact_number'      => $phone(),
                'gender'              => $isMale ? 'Male' : 'Female',
                'birthdate'           => $birth->format('Y-m-d'),
                'age'                 => $when->year - (int) $birth->format('Y'),
                'source'              => $faker->randomElement($sources),
                'address'             => 'Purok ' . mt_rand(1, 7) . ', Brgy. ' . $faker->randomElement($this->barangays)
                    . ', ' . $faker->randomElement($this->towns) . ', Albay',
                'created_by'          => $agent,
                'updated_by'          => $agent,
                'created_at'          => $when,
                'updated_at'          => $when,
            ]);
        };

        // ---- Leads (inquiries), one new customer each ----------------------
        // ~16 leads a month in January growing to ~31 by the latest month.
        $inquiries = [];    // list of [id, agent, customer, vehicle, month]
        foreach ($months as $m) {
            $count = (int) round(26 * $growth($m));

            for ($i = 0; $i < $count; $i++) {
                $agent    = $pickAgent();
                $created  = $dateIn($m);
                $updated  = $capToNow($created->copy()->addDays(mt_rand(0, 3)));
                if ($updated->month !== $m) {
                    $updated = $created->copy();
                }
                $customer = $makeCustomer($agent, $created);
                $vehicle  = $faker->randomElement($vehicleIds);
                $typeId   = DB::table('customer')->where('id', $customer)->value('inquiry_type_id');

                $id = DB::table('inquiry')->insertGetId([
                    'inquiry_type_id'   => $typeId,
                    'customer_id'       => $customer,
                    'vehicle_id'        => $vehicle,
                    // Must match the edit form's option values, or the
                    // "Inquiry Details" modal shows these fields empty.
                    'transaction'       => $faker->randomElement(['financing', 'financing', 'financing', 'cash', 'cash', 'po']),
                    'category'          => $faker->randomElement(['Hot', 'Warm', 'Warm', 'Cold']),
                    'quantity'          => $typeId == ($types['Individual'] ?? 0) ? 1 : $faker->numberBetween(2, 5),
                    'remarks'           => $faker->optional(0.35)->randomElement([
                        'Asked for a test drive this weekend.', 'Comparing financing offers from two banks.',
                        'Waiting for spouse approval.', 'Wants the unit before the holidays.',
                        'Requested a quotation with the free accessories package.', 'Trade-in of an old unit.',
                    ]),
                    'date'              => $created->format('F j'),
                    'status_id'         => (string) $faker->randomElement($leadStatuses),
                    'notif_status'      => $m === $now->month ? $faker->randomElement(['open', 'read']) : 'read',
                    'is_dispute'        => 0,
                    'created_by'        => $agent,
                    'status_updated_by' => $agent,
                    'status_updated_at' => $updated->format('Y-m-d'),
                    'updated_by'        => $agent,
                    'created_at'        => $created,
                    'updated_at'        => $updated,
                ]);

                DB::table('customer')->where('id', $customer)->update(['inquiry_id' => $id]);
                $inquiries[] = compact('id', 'agent', 'customer', 'vehicle') + ['month' => $m];
            }
        }

        // ---- Disputes (a second agent claims an existing customer) ----------
        // Built from a real base lead with a DIFFERENT agent and a status other
        // than Processed, which the dispute query excludes.
        $disputeStatuses = array_values(array_filter([$s('Pending'), $s('Approved')]));
        $recent = array_values(array_filter($inquiries, fn ($q) => $q['month'] >= $now->month - 1));
        shuffle($recent);
        foreach (array_slice($recent, 0, 8) as $base) {
            $other = $faker->randomElement(array_values(array_diff($agentIds, [$base['agent']])));
            $when  = $dateIn($now->month);

            DB::table('inquiry')->insert([
                'inquiry_type_id'   => DB::table('inquiry')->where('id', $base['id'])->value('inquiry_type_id'),
                'customer_id'       => $base['customer'],
                'vehicle_id'        => $base['vehicle'],
                'transaction'       => $faker->randomElement(['financing', 'cash']),
                'category'          => 'Hot',
                'quantity'          => 1,
                'remarks'           => 'Disputed: another agent is also handling this client.',
                'date'              => $when->format('F j'),
                'status_id'         => (string) $faker->randomElement($disputeStatuses),
                'notif_status'      => 'open',
                'is_dispute'        => 1,
                'created_by'        => $other,
                'status_updated_by' => $other,
                'status_updated_at' => $when->format('Y-m-d'),
                'updated_by'        => $other,
                'created_at'        => $when,
                'updated_at'        => $when,
            ]);
        }

        // ---- Applications + transactions -----------------------------------
        // A share of each month's leads move on to an application. Each one gets
        // exactly one transaction (the application module reads
        // transactions->first() and freezes when it is missing).
        //
        // Outcomes by month:
        //   older months  -> mostly Released/Posted (closed sales)
        //   last 2 months -> a mix of Reserved, Pending For Release, in process
        // Released units are counted by MONTH(updated_at) on the dashboard and
        // by the current month on the ranking page.
        $folder = 1000;
        $reservation = 5000;
        $appCount = 0;
        $released = 0;

        foreach ($months as $m) {
            $pool = array_values(array_filter($inquiries, fn ($q) => $q['month'] === $m));
            shuffle($pool);
            $take = (int) round(count($pool) * ($m === $now->month ? 0.9 : 0.55));
            $monthsAgo = $now->month - $m;

            foreach (array_slice($pool, 0, $take) as $q) {
                // Decide where this deal is in the flow.
                $roll = mt_rand(1, 100);
                if ($monthsAgo >= 2) {
                    $stage = $roll <= 78 ? 'released' : ($roll <= 88 ? 'reserved' : 'process');
                } elseif ($monthsAgo === 1) {
                    $stage = $roll <= 60 ? 'released' : ($roll <= 75 ? 'reserved' : ($roll <= 88 ? 'pending' : 'process'));
                } else {
                    $stage = $roll <= 65 ? 'released' : ($roll <= 77 ? 'reserved' : ($roll <= 90 ? 'pending' : 'process'));
                }

                $txType = $faker->randomElement(['Financing', 'Financing', 'Financing', 'Cash', 'Cash', 'PO']);
                $isFin  = $txType === 'Financing';

                $appDate = $dateIn($m);
                $updated = $stage === 'released'
                    ? $capToNow($appDate->copy()->addDays(mt_rand(3, 12)))
                    : $capToNow($appDate->copy()->addDays(mt_rand(0, 5)));
                if ($updated->month !== $m) {
                    $updated = $appDate->copy();
                }

                $status = match ($stage) {
                    'released' => $faker->randomElement($releaseStatuses),
                    'reserved' => $s('Reserved'),
                    'pending'  => $s('Pending For Release'),
                    default    => $faker->randomElement($inProcessStatuses),
                };
                $isReservation = $stage !== 'process';

                $appId = DB::table('application')->insertGetId([
                    'customer_id'  => $q['customer'],
                    'vehicle_id'   => $q['vehicle'],
                    'status_id'    => $stage === 'process' ? $status : $s('Approved'),
                    'bank_id'      => $isFin && $bankIds ? $faker->randomElement($bankIds) : null,
                    'terms'        => $isFin ? $faker->randomElement(['36', '48', '60', '60']) : null,
                    'percentage'   => $isFin ? $faker->randomElement(['20', '20', '30', '50']) : null,
                    'transaction'  => $txType,
                    'remarks'      => null,
                    'notif_status' => $m === $now->month ? $faker->randomElement(['open', 'read']) : 'read',
                    'created_by'   => $q['agent'],
                    'updated_by'   => $q['agent'],
                    'created_at'   => $appDate,
                    'updated_at'   => $updated,
                ]);

                DB::table('transactions')->insert([
                    'folder_number'      => $folder++,
                    'inquiry_id'         => $q['id'],
                    'application_id'     => $appId,
                    'reservation_id'     => $isReservation ? $reservation++ : null,
                    'reservation_transaction_status' => $isReservation ? $status : null,
                    'reservation_status' => $isReservation ? 'reserved' : 'none',
                    'inventory_id'       => $inventoryIds ? $faker->randomElement($inventoryIds) : null,
                    'team_id'            => $agentTeam[$q['agent']],
                    'profit'             => $faker->randomFloat(2, 18000, 95000),
                    'other_profit'       => $faker->optional(0.5)->randomFloat(2, 2000, 15000),
                    'insurance'          => $faker->optional(0.6)->randomElement(['Toyota Insure', 'Malayan', 'Prudential Guarantee']),
                    'application_transaction_date' => $appDate->format('Y-m-d'),
                    'transaction_updated_date'     => $updated->format('Y-m-d'),
                    'reservation_date'   => $isReservation ? $appDate->copy()->addDays(mt_rand(1, 3))->min($updated)->format('Y-m-d') : null,
                    'released_date'      => $stage === 'released' ? $updated->format('Y-m-d') : null,
                    'released_remarks'   => $stage === 'released' ? $faker->optional(0.3)->randomElement([
                        'Released with complete documents.', 'Unit released with free accessories.',
                        'Released; LTO plates to follow.',
                    ]) : null,
                    'status'             => $status, // FK to status.id, never a name
                    'notif_status'       => $m === $now->month ? $faker->randomElement(['open', 'read']) : 'read',
                    'created_at'         => $appDate,
                    'updated_at'         => $updated,
                ]);

                $appCount++;
                if ($stage === 'released') {
                    $released++;
                }
            }
        }

        // ---- Backlog / archive records (previous years) ----------------------
        // Standalone archive tables. Their models relate to each other, so this
        // builds a self-consistent set instead of pointing at the live rows.
        $lastYearFrom = Carbon::create($year - 1, 1, 1);
        $lastYearTo   = Carbon::create($year - 1, 12, 31);

        $invBacklogIds = [];
        for ($i = 0; $i < 20; $i++) {
            $agent = $agentIds[array_rand($agentIds)];
            $invBacklogIds[] = DB::table('inventory_backlogs')->insertGetId([
                'year_model'          => $year - 1,
                'vehicle_id'          => $faker->randomElement($vehicleIds),
                'CS_number'           => 'CSB' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'actual_invoice_date' => $faker->dateTimeBetween($lastYearFrom, $lastYearTo)->format('Y-m-d'),
                'delivery_date'       => $faker->dateTimeBetween($lastYearFrom, $lastYearTo)->format('Y-m-d'),
                'invoice_number'      => 'INVB' . str_pad($i + 1, 5, '0', STR_PAD_LEFT),
                'age'                 => $faker->numberBetween(1, 24),
                'status'              => 'Released',
                'CS_number_status'    => 'Released',
                'incoming_status'     => 'On Stock',
                'tag'                 => $agent, // InventoryBacklog::user() joins on tag
                'team_id'             => $agentTeam[$agent],
                'remarks'             => null,
                'created_by'          => $agent,
                'updated_by'          => $agent,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);
        }

        $customerIds = array_column($inquiries, 'customer');

        $inqBacklogIds = [];
        for ($i = 0; $i < 25; $i++) {
            $agent = $agentIds[array_rand($agentIds)];
            $when  = $faker->dateTimeBetween($lastYearFrom, $lastYearTo);
            $inqBacklogIds[] = DB::table('inquiry_backlogs')->insertGetId([
                'inquiry_type_id'   => $faker->randomElement($typeMix),
                'customer_id'       => $faker->randomElement($customerIds),
                'vehicle_id'        => $faker->randomElement($vehicleIds),
                'transaction'       => $faker->randomElement(['financing', 'cash']),
                'category'          => $faker->randomElement(['Hot', 'Warm', 'Cold']),
                'quantity'          => 1,
                'remarks'           => null,
                'date'              => $when->format('F j'),
                'status_id'         => (string) $s('Processed'),
                'is_dispute'        => 0,
                'created_by'        => $agent,
                'status_updated_by' => $agent,
                'status_updated_at' => $when->format('Y-m-d'),
                'updated_by'        => $agent,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
        }

        $appBacklogIds = [];
        for ($i = 0; $i < 25; $i++) {
            $agent = $agentIds[array_rand($agentIds)];
            $appBacklogIds[] = DB::table('application_backlogs')->insertGetId([
                'customer_id' => $faker->randomElement($customerIds),
                'vehicle_id'  => $faker->randomElement($vehicleIds),
                'status_id'   => $s('Approved'),
                'bank_id'     => $bankIds ? $faker->randomElement($bankIds) : null,
                'terms'       => $faker->randomElement(['36', '48', '60']),
                'percentage'  => $faker->randomElement(['20', '30', '50']),
                'transaction' => 'Financing',
                'remarks'     => null,
                'created_by'  => $agent,
                'updated_by'  => $agent,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }

        // The Releases Backlogs module filters on reservation_transaction_status
        // IN (Released, Posted, Pending For Release) with reservation_id set.
        for ($i = 0; $i < 25; $i++) {
            $status  = $faker->randomElement($releaseStatuses);
            $appDate = Carbon::instance($faker->dateTimeBetween($lastYearFrom, $lastYearTo->copy()->subDays(20)));
            $relDate = $appDate->copy()->addDays(mt_rand(5, 20));

            DB::table('transaction_backlogs')->insert([
                'folder_number'      => 2000 + $i,
                'inquiry_id'         => $faker->randomElement($inqBacklogIds),
                'application_id'     => $faker->randomElement($appBacklogIds),
                'reservation_id'     => 6000 + $i,
                'reservation_transaction_status' => $status,
                'reservation_status' => 'reserved',
                'inventory_id'       => $faker->randomElement($invBacklogIds),
                'team_id'            => $teamIds ? $faker->randomElement($teamIds) : null,
                'profit'             => $faker->randomFloat(2, 18000, 95000),
                'application_transaction_date' => $appDate->format('Y-m-d'),
                'transaction_updated_date'     => $relDate->format('Y-m-d'),
                'reservation_date'   => $appDate->copy()->addDays(2)->format('Y-m-d'),
                'released_date'      => $relDate->format('Y-m-d'),
                'released_remarks'   => null,
                'status'             => $status, // FK to status.id
                'lto_remarks'        => $faker->optional(0.3)->randomElement(['Plates released.', 'OR/CR forwarded to client.']),
                'notif_status'       => 'read',
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        $this->command->info(sprintf(
            'CrmSampleSeeder: %d agents, %d leads, 8 disputes, %d applications (%d released units) for Jan-%s %d.',
            count($agentIds), count($inquiries), $appCount, $released, $now->format('M'), $year
        ));
        $this->command->info('CrmSampleSeeder: 20 inventory, 25 inquiry, 25 application, 25 transaction backlog records (last year).');
    }
}
