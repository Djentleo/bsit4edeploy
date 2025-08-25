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

        // Load the trained model
        $modelPath = str_replace('\\', '/', base_path('incident_classifier.joblib'));
        if (!file_exists($modelPath)) {
            echo "Trained model not found at: $modelPath\n";
            return;
        }

        $incidents = [
            [
                'incident_id' => '1',
                'incident_description' => 'Car crash on the highway',
                'location' => 'J.P. Rizal Street, Baritan, Malabon',
                'reporter_name' => 'Juan Dela Cruz',
                'source' => 'mobile',
                'severity' => 'high',
                'status' => 'new',
                'timestamp' => now()->toIso8601String(),
            ],
            [
                'incident_id' => '2',
                'incident_description' => 'Fire alarm triggered in the basement',
                'location' => 'M.H. Del Pilar Street, Baritan, Malabon',
                'reporter_name' => 'Maria Clara',
                'source' => 'cctv_ai',
                'severity' => 'medium',
                'status' => 'dispatched',
                'timestamp' => now()->toIso8601String(),
            ],
            [
                'incident_id' => '3',
                'incident_description' => 'Flooding reported in the parking lot',
                'location' => 'Gen. Luna Street, Baritan, Malabon',
                'reporter_name' => 'Jose Rizal',
                'source' => 'mobile',
                'severity' => 'low',
                'status' => 'resolved',
                'timestamp' => now()->toIso8601String(),
            ],
            [
                'incident_id' => '4',
                'incident_description' => 'Medical emergency at the mall',
                'location' => 'SM Center, Malabon',
                'reporter_name' => 'Pedro Penduko',
                'source' => 'mobile',
                'severity' => 'high',
                'status' => 'new',
                'timestamp' => now()->toIso8601String(),
            ],
            [
                'incident_id' => '5',
                'incident_description' => 'Loud music disturbing the neighborhood',
                'location' => 'Barangay Hall, Baritan, Malabon',
                'reporter_name' => 'Aling Nena',
                'source' => 'mobile',
                'severity' => 'low',
                'status' => 'resolved',
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        $typeToDepartment = [
            'vehicle_crash' => 'Traffic Management',
            'fire' => 'Fire Department',
            'flood' => 'Maintenance',
            'public_disturbance' => 'Community Affairs',
            'healthcare' => 'Healthcare',
        ];

        foreach ($incidents as &$incident) {
            // Predict the type using the model
            $process = new \Symfony\Component\Process\Process([
                'python', '-c', "import joblib; model = joblib.load('$modelPath'); print(model.predict(['" . addslashes($incident['incident_description']) . "'])[0])"
            ]);
            $process->run();

            if ($process->isSuccessful()) {
                $predictedType = trim($process->getOutput());
                $incident['type'] = $predictedType;
                $incident['department'] = $typeToDepartment[$predictedType] ?? 'Unknown';
            } else {
                Log::error("Failed to predict type for incident: " . $incident['incident_description']);
                $incident['type'] = 'unknown';
                $incident['department'] = 'Unknown';
            }
        }

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
