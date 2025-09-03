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

        $database = $firebase->getReference('incidents');

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
                [
                    'address' => 'J.P. Rizal Street, Baritan, Malabon',
                    'latitude' => 14.6621,
                    'longitude' => 120.9566
                ],
                [
                    'address' => 'M.H. Del Pilar Street, Baritan, Malabon',
                    'latitude' => 14.6602,
                    'longitude' => 120.9551
                ],
                [
                    'address' => 'Gen. Luna Street, Baritan, Malabon',
                    'latitude' => 14.6587,
                    'longitude' => 120.9540
                ],
                [
                    'address' => 'SM Center, Malabon',
                    'latitude' => 14.6562,
                    'longitude' => 120.9532
                ],
                [
                    'address' => 'Barangay Hall, Baritan, Malabon',
                    'latitude' => 14.6610,
                    'longitude' => 120.9570
                ],
                [
                    'address' => 'Rizal Avenue, Malabon',
                    'latitude' => 14.6550,
                    'longitude' => 120.9520
                ],
                [
                    'address' => 'Imelda Avenue, Malabon',
                    'latitude' => 14.6535,
                    'longitude' => 120.9510
                ],
                [
                    'address' => 'Market Area, Malabon',
                    'latitude' => 14.6545,
                    'longitude' => 120.9505
                ],
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
                    'latitude' => $loc['latitude'],
                    'longitude' => $loc['longitude'],
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
