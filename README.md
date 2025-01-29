FireBaseNotificationPHP

A Laravel package for sending Firebase Cloud Messaging (FCM) notifications easily.

Installation

1. Install the Package

You can install the package via Composer:

```
composer require sendfirebase/notificationphp

```

2. Publish the Configuration File

Run the following command to publish the firebase.php config file:

```
php artisan vendor:publish --provider="SendFireBaseNotificationPHP\Providers\FireBaseNotificationServiceProvider" --tag="config"

```

3. Configure Firebase Credentials

Add your Firebase credentials in .env:

```
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_API_VERSION=v1

```

Move your Firebase service account JSON file to:

storage/app/firebase/firebase_credentials.json

```
mkdir -p storage/app/firebase && touch storage/app/firebase/firebase_credentials.json

```

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

Contributing

How to Improve the Package and Make a Pull Request (PR)

Fork the Repository

Go to the GitHub repository.

Click on the "Fork" button to create a copy of the repository in your account.

Clone Your Fork Locally

```
git clone https://github.com/YacoubAl-hardari/firebasenotificationphp.git

```

Then Access to the Folder And  Create a New Branch

```
git checkout -b feature/your-feature-name

```

Make Your Changes

Improve the code, fix bugs, or add new features.

Update the README.md if needed.

Commit Your Changes
```
git add .
```

```
git commit -m "Added new feature: [describe your feature]"
```
Push Your Branch to GitHub

```
git push origin feature/your-feature-name

```
Create a Pull Request (PR)

Go to your repository on GitHub.

Click on "Compare & pull request".

Add a title and description for your PR.

Click "Create pull request".

Wait for Review

The maintainers will review your PR and provide feedback.

Make any requested changes and push them to your branch.

Once approved, your changes will be merged!

License

This package is open-source and available under the MIT license.
