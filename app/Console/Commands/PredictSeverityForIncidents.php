<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Http;

class PredictSeverityForIncidents extends Command
{
    protected $signature = 'incidents:predict-severity';
    protected $description = 'Predict and update severity/priority for incidents missing these fields in Firebase';

    public function handle()
    {
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'))
            ->createDatabase();

        $ref = $firebase->getReference('mobile_incidents');
        $incidents = $ref->getValue() ?? [];
        $updated = 0;

        foreach ($incidents as $id => $incident) {
            $desc = $incident['incident_description'] ?? $incident['description'] ?? null;
            if (empty($incident['severity']) && !empty($desc)) {
                $response = Http::post('http://127.0.0.1:5000/predict-severity', [
                    'description' => $desc
                ]);
                $severity = $response->json('severity') ?? 'unknown';
                $priority = $severity;
                $firebase->getReference('mobile_incidents/' . $id)
                    ->update(['severity' => $severity, 'priority' => $priority]);
                $this->info("Updated incident $id with severity: $severity");
                $updated++;
            }
        }
        $this->info("Done. $updated incidents updated.");
    }
}
