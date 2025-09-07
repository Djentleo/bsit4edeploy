<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FirebaseService
{
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));

        $this->database = $factory->createDatabase();
    }

    /**
     * Get all incidents from both mobile_incidents and incidents (CCTV) as a merged array.
     */
    public function getAllIncidents()
    {
        $mobile = $this->database->getReference('mobile_incidents')->getValue() ?? [];
        $cctv = $this->database->getReference('incidents')->getValue() ?? [];
        // Add a source property for each
        $mobileList = collect($mobile)->map(function($item) {
            $item['source'] = 'mobile';
            return $item;
        })->values()->all();
        $cctvList = collect($cctv)->map(function($item) {
            $item['source'] = 'cctv';
            return $item;
        })->values()->all();
        // Merge and return
        return array_merge($mobileList, $cctvList);
    }
        public function getIncidentById($incidentId)
        {
            // Try mobile_incidents first, then fallback to incidents (for CCTV)
            $mobile = $this->database->getReference('mobile_incidents/' . $incidentId)->getValue();
            if ($mobile) return $mobile;
            return $this->database->getReference('incidents/' . $incidentId)->getValue();
        }
    /**
     * Get summary counts for dashboard (total, current, completed issues) from both sources.
     */
    public function getSummaryData()
    {
        $all = $this->getAllIncidents();
        $total = count($all);
        $current = collect($all)->where('status', '!=', 'resolved')->count();
        $completed = collect($all)->where('status', 'resolved')->count();
        return [
            'total_cases' => $total,
            'current_issues' => $current,
            'completed_issues' => $completed,
        ];
    }
}
