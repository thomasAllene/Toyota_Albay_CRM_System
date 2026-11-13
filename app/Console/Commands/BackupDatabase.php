<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database and save it to the storage folder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get database credentials from .env
        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPassword = env('DB_PASSWORD');

        // Define the backup file name and path
        $fileName = 'backup_toyota_db_' . date('Y-m-d_H-i-s') . '.sql';
        $filePath = storage_path('app/backups/' . $fileName);

        // Ensure the backups directory exists
        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $command = sprintf(
            'mysqldump --user=%s --host=%s --port=%s %s > %s',
            escapeshellarg($dbUser),
            // escapeshellarg($dbPassword),
            escapeshellarg($dbHost),
            escapeshellarg($dbPort),
            escapeshellarg($dbName),
            escapeshellarg($filePath)
        );

        // Execute the command
        $result = null;
        $output = null;
        exec($command, $output, $result);

        if ($result === 0) {
            $this->info('Database backup created successfully: ' . $fileName);
        } else {
            $this->error('Failed to create database backup. Error: ' . implode("\n", $output));
            Log::error('Backup command failed: ' . $command);
            Log::error('Output: ' . implode("\n", $output));
        }
    }
    
}
