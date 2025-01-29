FireBaseNotificationPHP

A Laravel package for sending Firebase Cloud Messaging (FCM) notifications easily.

Installation

1. Install the Package

You can install the package via Composer:

composer require sendfirebase/notificationphp

2. Publish the Configuration File

Run the following command to publish the firebase.php config file:

```
php artisan vendor:publish --provider="SendFireBaseNotificationPHP\Providers\FireBaseNotificationServiceProvider" --tag="config"

```

3. Configure Firebase Credentials

Add your Firebase credentials in .env:

FIREBASE_PROJECT_ID=your_project_id
FIREBASE_API_VERSION=v1

Move your Firebase service account JSON file to:

storage/app/firebase/firebase_credentials.json

Make sure the firebase.php config file points to the correct location:

```

return [
    'project_id' => env('FIREBASE_PROJECT_ID', 'your_project_id'),
    'version' => env('FIREBASE_API_VERSION', 'v1'),
    'credentials_file' => storage_path('app/firebase/firebase_credentials.json'),
];

```


Usage

1. Inject FirebaseNotificationService in a Controller

Create a new controller or use an existing one:

```


    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

       if ($request->has('user_id')) {
                $response = $this->firebaseService->sendNotificationToSingle(
                    new User(),
                    $request->user_id,
                    $request->title,
                    $request->body,
                    'fcm_token'
                );
            } else {
                $response = $this->firebaseService->sendNotificationToAll(
                    new User(),
                    $request->title,
                    $request->body,
                    'fcm_token'
                );
            }

```


License

This package is open-source and available under the MIT license.

