<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Kreait\Firebase\Factory;
use Illuminate\Support\Str;

class CctvIncidentSeeder extends Seeder
{
    public function run()
    {
        $firebase = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'))
            ->createDatabase();


        $reference = $firebase->getReference('incidents');
        // Delete all existing CCTV incidents before seeding

        $cameraNames = ['cam ni gab'];
        $cameraUrls = [
            'http://192.168.1.3:8700/video',
            'http://192.168.1.4:8700/video',
            'http://192.168.1.5:8700/video',
            'http://192.168.1.6:8700/video',
        ];
        $events = ['natural_disaster', 'fire', 'disturbance', 'vehicular_accident'];
        $statuses = 'new';

        foreach (range(1, 5) as $i) {
            $cameraIdx = array_rand($cameraNames);
            $eventIdx = array_rand($events);
            // Generate a random date in 2024
            $randomTimestamp = now()->setDate(2023, rand(1, 12), rand(1, 28))->setTime(rand(0,23), rand(0,59), rand(0,59));
            $incident = [
                'camera_name' => $cameraNames[$cameraIdx],
                'camera_url' => $cameraUrls[$cameraIdx],
                'event' => $events[$eventIdx],
                'screenshot_path' => 'D:\\Capstone\\NEW\\incident_screenshots\\' . $randomTimestamp->format('Ymd_His') . '_' . $events[$eventIdx] . '.jpg',
                'status' => $statuses,
                'timestamp' => $randomTimestamp->format('h:i:s A Y-m-d'),
            ];
            $reference->push($incident);
        }
    }
}
