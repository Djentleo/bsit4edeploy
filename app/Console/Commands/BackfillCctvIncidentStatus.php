<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;

class BackfillCctvIncidentStatus extends Command
{
    protected $signature = 'firebase:backfill-cctv-status';
    protected $description = 'Add status field to all CCTV incidents in Firebase if missing';

    public function handle()
    {
        $this->info('Connecting to Firebase...');
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'))
            ->createDatabase();

        $ref = $firebase->getReference('incidents');
        $incidents = $ref->getValue();
        if (!$incidents || !is_array($incidents)) {
            $this->info('No CCTV incidents found.');
            return;
        }
        $updated = 0;
        foreach ($incidents as $key => $incident) {
            if (!isset($incident['status'])) {
                $ref->getChild($key)->update(['status' => 'new']);
                $this->info("Added status to incident: $key");
                $updated++;
            }
        }
        $this->info("Backfill complete. {$updated} incidents updated.");
    }
}
