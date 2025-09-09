<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class IncidentSeeder extends Seeder
{
    public function run()
    {
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'))
            ->createDatabase();

        $database = $firebase->getReference('mobile_incidents');

        // Clear existing records in the 'incidents' node
        try {
            $database->remove();
            echo "All existing records in 'incidents' have been removed.\n";
        } catch (\Exception $e) {
            echo "Error clearing records: " . $e->getMessage() . "\n";
        }
        // Programmatically generate 75 simulation incidents with varied fields
        $types = ['vehicle_crash', 'fire', 'disturbance', 'medical_emergency'];
        $departments = [
            'vehicle_crash' => 'Traffic Management',
            'fire' => 'Fire Department',
            'disturbance' => 'Police',
            'medical_emergency' => 'Health',
        ];
        $locations = [
            ['address' => 'J.P. Rizal Street, Baritan, Malabon'],
            ['address' => 'M.H. Del Pilar Street, Baritan, Malabon'],
            ['address' => 'Gen. Luna Street, Baritan, Malabon'],
            ['address' => 'SM Center, Malabon'],
            ['address' => 'Barangay Hall, Baritan, Malabon'],
            ['address' => 'Rizal Avenue, Malabon'],
            ['address' => 'Imelda Avenue, Malabon'],
            ['address' => 'Market Area, Malabon'],
        ];
        $reporters = ['Juan Dela Cruz', 'Maria Clara', 'Jose Rizal', 'Pedro Penduko', 'Aling Nena', 'Anna Santos', 'Miguel Tan', 'Carmen Reyes'];
        $statuses = ['new', 'dispatched', 'resolved'];

        for ($i = 1; $i <= 50; $i++) {
            $type = $types[array_rand($types)];
            $loc = $locations[array_rand($locations)];

            // More realistic descriptions for each type
            $descTemplates = [
                'vehicle_crash' => [
                    'Multi-vehicle accident at ' . $loc['address'],
                    'Car collision reported near ' . $loc['address'],
                    'Vehicular crash involving multiple cars at ' . $loc['address'],
                    'Major road accident at ' . $loc['address'],
                ],
                'fire' => [
                    'Fire alarm triggered at ' . $loc['address'],
                    'Smoke and flames seen at ' . $loc['address'],
                    'Building fire reported at ' . $loc['address'],
                    'Fire outbreak in the area of ' . $loc['address'],
                ],
                'disturbance' => [
                    'Public disturbance reported at ' . $loc['address'],
                    'Loud altercation at ' . $loc['address'],
                    'Unauthorized gathering at ' . $loc['address'],
                    'Suspicious activity at ' . $loc['address'],
                ],
                'medical_emergency' => [
                    'Medical emergency at ' . $loc['address'],
                    'Person collapsed at ' . $loc['address'],
                    'Injury reported at ' . $loc['address'],
                    'Urgent medical assistance needed at ' . $loc['address'],
                ],
            ];
            $incident = [
                'type' => $type,
                'incident_description' => $descTemplates[$type][array_rand($descTemplates[$type])],
                'location' => $loc['address'],
                'reporter_name' => $reporters[array_rand($reporters)],
                'department' => $departments[$type] ?? 'General',
                // 'severity' => $severities[array_rand($severities)],
                'status' => $statuses[array_rand($statuses)],
                // spread timestamps over recent time
                'timestamp' => now()->subMinutes(rand(0, 60 * 24 * 30))->toIso8601String(),
            ];

            // Call Flask API for severity prediction
            /*
            try {
                $response = Http::post('http://127.0.0.1:5000/predict-severity', [
                    'description' => $incident['incident_description']
                ]);
                $incident['severity'] = $response->json('severity') ?? 'unknown';
                $incident['priority'] = $incident['severity'];
            } catch (\Exception $e) {
                $incident['severity'] = 'unknown';
                $incident['priority'] = 'unknown';
                echo "Error predicting severity: " . $e->getMessage() . "\n";
            }
            */
            try {
                $newRef = $database->push($incident);
                $incidentId = $newRef->getKey();
                $newRef->update(['incident_id' => $incidentId]);
                echo "Record added with id: " . $incidentId . "\n";
            } catch (\Exception $e) {
                echo "Error adding record: " . $e->getMessage() . "\n";
            }
        }
    }
}
