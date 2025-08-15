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
        return $this->database->getReference('incidents')->getValue();
    }
    public function getSummaryData()
    {
        return $this->database->getReference('summary')->getValue();
    }
}
