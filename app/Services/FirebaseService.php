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

    public function getIncidents()
    {
    return $this->database->getReference('mobile_incidents')->getValue();
    }
        public function getIncidentById($incidentId)
        {
            // Try mobile_incidents first, then fallback to incidents (for CCTV)
            $mobile = $this->database->getReference('mobile_incidents/' . $incidentId)->getValue();
            if ($mobile) return $mobile;
            return $this->database->getReference('incidents/' . $incidentId)->getValue();
        }
    public function getSummaryData()
    {
        return $this->database->getReference('summary')->getValue();
    }
}
