<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Log;

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
                [ 'address' => 'J.P. Rizal Street, Baritan, Malabon' ],
                [ 'address' => 'M.H. Del Pilar Street, Baritan, Malabon' ],
                [ 'address' => 'Gen. Luna Street, Baritan, Malabon' ],
                [ 'address' => 'SM Center, Malabon' ],
                [ 'address' => 'Barangay Hall, Baritan, Malabon' ],
                [ 'address' => 'Rizal Avenue, Malabon' ],
                [ 'address' => 'Imelda Avenue, Malabon' ],
                [ 'address' => 'Market Area, Malabon' ],
            ];
            $reporters = ['Juan Dela Cruz', 'Maria Clara', 'Jose Rizal', 'Pedro Penduko', 'Aling Nena', 'Anna Santos', 'Miguel Tan', 'Carmen Reyes'];
            $severities = ['low', 'medium', 'high'];
            $sources = ['mobile', 'cctv_ai', 'hotline'];
            $statuses = ['new', 'dispatched', 'resolved'];

            for ($i = 1; $i <= 50; $i++) {
                $type = $types[array_rand($types)];
                $loc = $locations[array_rand($locations)];
                $incident = [
                    'incident_id' => (string) $i,
                    'type' => $type,
                    'incident_description' => ucfirst($type) . ' reported (#' . $i . ')',
                    'location' => $loc['address'],
                    'reporter_name' => $reporters[array_rand($reporters)],
                    'department' => $departments[$type] ?? 'General',
                    'severity' => $severities[array_rand($severities)],
                    'source' => $sources[array_rand($sources)],
                    'status' => $statuses[array_rand($statuses)],
                    // spread timestamps over recent time
                    'timestamp' => now()->subMinutes(rand(0, 60 * 24 * 30))->toIso8601String(),
                ];

                try {
                    $database->getChild($incident['incident_id'])->set($incident);
                    echo "Record added with id: " . $incident['incident_id'] . "\n";
                } catch (\Exception $e) {
                    echo "Error adding record: " . $e->getMessage() . "\n";
                }
            }
    }
}
