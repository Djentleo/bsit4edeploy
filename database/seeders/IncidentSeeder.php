<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Kreait\Firebase\Factory;

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

        $incidents = [
            [
                'incident_id' => '1',
                'type' => 'vehicular_accident',
                'location' => 'J.P. Rizal Street, Baritan, Malabon',
                'reporter_name' => 'Juan Dela Cruz',
                'source' => 'mobile',
                'severity' => 'high',
                'status' => 'new',
                'timestamp' => now()->toIso8601String(),
                'department' => 'police',
            ],
            [
                'incident_id' => '2',
                'type' => 'fire',
                'location' => 'M.H. Del Pilar Street, Baritan, Malabon',
                'reporter_name' => 'Maria Clara',
                'source' => 'cctv_ai',
                'severity' => 'medium',
                'status' => 'dispatched',
                'timestamp' => now()->toIso8601String(),
                'department' => 'fire',
            ],
            [
                'incident_id' => '3',
                'type' => 'medical_emergency',
                'location' => 'Gen. Luna Street, Baritan, Malabon',
                'reporter_name' => 'Jose Rizal',
                'source' => 'mobile',
                'severity' => 'low',
                'status' => 'resolved',
                'timestamp' => now()->toIso8601String(),
                'department' => 'health',
            ],
            // Add 17 more incidents with unique data
            ...array_map(function ($i) {
                return [
                    'incident_id' => (string)($i + 3),
                    'type' => ['vehicular_accident', 'fire', 'medical_emergency'][array_rand(['vehicular_accident', 'fire', 'medical_emergency'])],
                    'location' => ['J.P. Rizal Street', 'M.H. Del Pilar Street', 'Gen. Luna Street'][array_rand(['J.P. Rizal Street', 'M.H. Del Pilar Street', 'Gen. Luna Street'])] . ', Baritan, Malabon',
                    'reporter_name' => ['Juan Dela Cruz', 'Maria Clara', 'Jose Rizal'][array_rand(['Juan Dela Cruz', 'Maria Clara', 'Jose Rizal'])],
                    'source' => ['mobile', 'cctv_ai'][array_rand(['mobile', 'cctv_ai'])],
                    'severity' => ['low', 'medium', 'high'][array_rand(['low', 'medium', 'high'])],
                    'status' => ['new', 'dispatched', 'resolved'][array_rand(['new', 'dispatched', 'resolved'])],
                    'timestamp' => now()->toIso8601String(),
                    'department' => ['police', 'fire', 'health'][array_rand(['police', 'fire', 'health'])],
                ];
            }, range(1, 17))
        ];

        foreach ($incidents as $incident) {
            try {
                $result = $database->push($incident);
                echo "Record added with key: " . $result->getKey() . "\n";
            } catch (\Exception $e) {
                echo "Error adding record: " . $e->getMessage() . "\n";
            }
        }

        // Test Firebase connection
        try {
            $testRecord = [
                'incident_id' => 'connection_test',
                'type' => 'test_type',
                'location' => 'test_location',
                'reporter_name' => 'test_name',
                'source' => 'test_source',
                'severity' => 'test_severity',
                'status' => 'test_status',
                'timestamp' => now()->toIso8601String(),
                'department' => 'test_department',
            ];
            $result = $database->push($testRecord);
            echo "Connection test record added with key: " . $result->getKey() . "\n";
        } catch (\Exception $e) {
            echo "Error adding connection test record: " . $e->getMessage() . "\n";
        }
    }
}
