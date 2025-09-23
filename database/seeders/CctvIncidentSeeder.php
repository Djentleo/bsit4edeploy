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
        $reference->remove();

        $cameraNames = ['cam ni gab'];
        $cameraUrls = [
            'http://192.168.1.3:8700/video',
            'http://192.168.1.4:8700/video',
            'http://192.168.1.5:8700/video',
            'http://192.168.1.6:8700/video',
        ];
        $events = ['natural_disaster', 'fire', 'disturbance', 'vehicular_accident'];
        $statuses = ['new', 'dispatched', 'resolved'];

        foreach (range(1, 5) as $i) {
            $cameraIdx = array_rand($cameraNames);
            $eventIdx = array_rand($events);
            $statusIdx = array_rand($statuses);
            $incident = [
                'camera_name' => $cameraNames[$cameraIdx],
                'camera_url' => $cameraUrls[$cameraIdx],
                'event' => $events[$eventIdx],
                'screenshot_path' => 'D:\\Capstone\\NEW\\incident_screenshots\\' . date('Ymd_His') . '_' . $events[$eventIdx] . '.jpg',
                'status' => $statuses[$statusIdx],
                'timestamp' => now()->format('h:i:s A Y-m-d'),
            ];
            $reference->push($incident);
        }
    }
}
