<?php

require 'vendor/autoload.php';

use Kreait\Firebase\Factory;

try {
    echo "Initializing Firebase connection...\n";
    $firebase = (new Factory)->withServiceAccount(__DIR__ . '/storage/app/firebase_credentials.json')->createFirestore();
    echo "Firebase connection initialized.\n";

    $database = $firebase->database();
    echo "Database instance created.\n";

    $record = [
        'incident_id' => 'standalone_test',
        'type' => 'test_type',
        'location' => 'test_location',
        'reporter_name' => 'test_name',
        'source' => 'test_source',
        'severity' => 'test_severity',
        'status' => 'test_status',
        'timestamp' => date('c'),
        'department' => 'test_department',
    ];

    echo "Attempting to add record to Firebase...\n";
    $result = $database->collection('incidents')->add($record);

    if ($result) {
        echo "Record successfully added with ID: " . $result->id() . "\n";
    } else {
        echo "Failed to add record.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
