<?php

namespace App\Services;

use Kreait\Firebase\Factory;


class FirebaseService
{
    /**
     * Create a new incident in Firebase with AI-predicted severity/priority.
     * Calls Flask API for prediction, adds fields, and pushes to mobile_incidents.
     * Returns the new incident data (including incident_id).
     */
    public function createIncidentWithPrediction(array $incident)
    {
        // Call Flask API for severity prediction
        try {
            $response = \Illuminate\Support\Facades\Http::post('http://127.0.0.1:5000/predict-severity', [
                'description' => $incident['incident_description'] ?? ''
            ]);
            $incident['severity'] = $response->json('severity') ?? 'unknown';
            $incident['priority'] = $incident['severity'];
        } catch (\Exception $e) {
            $incident['severity'] = 'unknown';
            $incident['priority'] = 'unknown';
        }

        // Push to Firebase
        $ref = $this->database->getReference('mobile_incidents');
        $newRef = $ref->push($incident);
        $incidentId = $newRef->getKey();
        $newRef->update(['incident_id' => $incidentId]);
        $incident['incident_id'] = $incidentId;
        return $incident;
    }
    protected $database;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'))
            ->withDatabaseUri(config('firebase.database_url'));

        $this->database = $factory->createDatabase();
    }



    /**
     * Alias for getAllIncidents for compatibility.
     */
    public function getIncidents()
    {
        return $this->getAllIncidents();
    }



    /**
     * Get all incidents from both mobile_incidents and incidents (CCTV) as a merged array.
     */
    public function getAllIncidents()
    {
        $mobile = $this->database->getReference('mobile_incidents')->getValue() ?? [];
        $cctv = $this->database->getReference('incidents')->getValue() ?? [];
        // Add a source property for each
        $mobileList = collect($mobile)->map(function ($item) {
            $item['source'] = 'mobile';
            return $item;
        })->values()->all();
        $cctvList = collect($cctv)->map(function ($item) {
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
