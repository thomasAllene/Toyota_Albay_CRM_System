<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;

class ResetInventoryTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:reset-tags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset inventory tags and team IDs daily at midnight';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Inventory::where('status', 'Ear Mark')
                ->update([
                    'tag' => null,
                    'team_id' => null,
                    'status' => 'Available',
                    'updated_at' => now(),
                    'updated_by' => 0  // System user ID
                ]);

            Log::info('Daily inventory tags reset completed successfully at ' . now());
            $this->info('Inventory tags reset successfully.');
        } catch(\Exception $e) {
            Log::error('Error in daily inventory tags reset: ' . $e->getMessage());
            $this->error('Failed to reset inventory tags.');
        }
    }
}
