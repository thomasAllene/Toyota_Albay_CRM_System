<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transactions;
use App\Models\Inventory;
use App\Models\Status;
use Carbon\Carbon;

class RevertCSNumberCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'csnumber:revert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revert CS number to available if not released within 2 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $twoDaysAgo = Carbon::now()->subDays(2);
            $reservation = Status::where('status', 'like', 'Reserved')->first()->id;
            // Fetch transactions that are not released and older than 2 days
            $transactions = Transactions::where('status', $reservation)
                ->whereNotNull('inventory_id')
                ->where('updated_at', '<=', $twoDaysAgo)
                ->get();

            if ($transactions->isEmpty()) {
                $this->info('No data on transaction.');
                return;
            }

        foreach ($transactions as $transaction) {
            if ($transaction->inventory_id) {
                $inventory = Inventory::find($transaction->inventory_id);
                if ($inventory) {
                    // Revert the CS number status to available
                    $inventory->tag =null;
                    $inventory->team_id=null;
                    $inventory->CS_number_status = 'Available';
                    $inventory->status = 'Available';
                    $inventory->updated_at = now();
                    $inventory->updated_by = 0;
                    // $inventory->timestamps = false; // Disable timestamps for this operation
                    $inventory->save();

                        // Optionally, you can also clear the inventory_id from the transaction
                        $transaction->inventory_id = null;
                        $transaction->timestamps = false; // Disable timestamps for this operation
                        $transaction->save();
                    }
                }
            }

            $this->info('CS numbers reverted to available for transactions not released within 2 days.');
        } catch (\Exception $e) {
            $this->error('An error occurred while reverting CS numbers: ' . $e->getMessage());
        }
    }
}
