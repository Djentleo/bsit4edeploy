<?php

namespace App\Console\Commands;


use Illuminate\Console\Command;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncIncidentLogsFromFirebase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:incident-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync resolved incidents from Firebase to MySQL incident_logs table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching resolved incidents from Firebase...');
        $firebase = app(FirebaseService::class);
        $resolved = $firebase->getResolvedIncidents();
        $count = 0;
        foreach ($resolved as $incident) {
            // Upsert into MySQL
            DB::table('incident_logs')->updateOrInsert(
                ['incident_id' => $incident['incident_id'] ?? null],
                [
                    'type' => $incident['type'] ?? null,
                    'location' => $incident['location'] ?? null,
                    'reporter_name' => $incident['reporter_name'] ?? null,
                    'reporter_id' => $incident['reporter_id'] ?? null,
                    'department' => $incident['department'] ?? null,
                    'status' => $incident['status'] ?? null,
                    'timestamp' => isset($incident['timestamp']) ? date('Y-m-d H:i:s', strtotime($incident['timestamp'])) : null,
                    'source' => $incident['source'] ?? null,
                    'incident_description' => $incident['incident_description'] ?? null,
                    'priority' => $incident['priority'] ?? null,
                    'severity' => $incident['severity'] ?? null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $count++;
        }
        $this->info("Sync complete. {$count} incidents upserted to incident_logs table.");
    }
}
