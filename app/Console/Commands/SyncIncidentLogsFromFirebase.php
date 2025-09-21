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
        // Get raw resolved_incidents with keys
        $factory = (new \Kreait\Firebase\Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));
        $database = $factory->createDatabase();
        $resolvedRaw = $database->getReference('resolved_incidents')->getValue() ?? [];
        $count = 0;
        foreach ($resolvedRaw as $firebaseKey => $incident) {
            $incidentId = $incident['incident_id'] ?? $incident['firebase_id'] ?? $firebaseKey;
            $isCctv = isset($incident['camera_name']) || ($incident['source'] ?? null) === 'cctv';
            DB::table('incident_logs')->updateOrInsert(
                ['incident_id' => $incidentId],
                [
                    'type' => $isCctv ? ($incident['event'] ?? 'CCTV') : ($incident['type'] ?? null),
                    'location' => $isCctv ? ($incident['camera_name'] ?? null) : ($incident['location'] ?? null),
                    'reporter_name' => $isCctv ? 'CCTV' : ($incident['reporter_name'] ?? null),
                    'reporter_id' => $isCctv ? null : ($incident['reporter_id'] ?? null),
                    'department' => $incident['department'] ?? null,
                    'status' => $incident['status'] ?? null,
                    'timestamp' => isset($incident['timestamp']) ? date('Y-m-d H:i:s', strtotime($incident['timestamp'])) : null,
                    'source' => $incident['source'] ?? ($isCctv ? 'cctv' : null),
                    'incident_description' => $isCctv ? ($incident['screenshot_path'] ?? null) : ($incident['incident_description'] ?? null),
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
