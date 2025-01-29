<?php

return [
    'project_id' => env('FIREBASE_PROJECT_ID', 'your_project_id'),
    'version' => env('FIREBASE_API_VERSION', 'v1'),
    'credentials_file' => storage_path('app/firebase/firebase_credentials.json'), // Path to credentials JSON file
];
