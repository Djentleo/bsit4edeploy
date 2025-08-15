<?php

return [
    'credentials' => storage_path('app/firebase_credentials.json'),
    'database_url' => env('FIREBASE_DATABASE_URL', 'https://your-database.firebaseio.com'),
];
