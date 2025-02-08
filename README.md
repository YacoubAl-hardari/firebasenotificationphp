# Firebase Notification Integration Package Documentation

A comprehensive solution for sending Firebase Cloud Messaging (FCM) notifications from a Laravel backend to Flutter applications.

![Firebase-Laravel-Flutter](https://storage.googleapis.com/assets.iankumu.com/2022/03/Laravel-Firebase.webp)

---

## Table of Contents

- [Laravel Integration](#laravel-integration)
  - [Prerequisites](#laravel-prerequisites)
  - [Installation](#laravel-installation)
  - [Configuration](#laravel-configuration)
  - [Usage Examples](#laravel-usage-examples)
  - [Troubleshooting](#laravel-troubleshooting)
- [Flutter Integration](#flutter-integration)
  - [Prerequisites](#flutter-prerequisites)
  - [Setup & Dependencies](#flutter-setup--dependencies)
  - [Notification Initialization and Handling](#flutter-notification-initialization-and-handling)
  - [Usage Examples](#flutter-usage-examples)
  - [Troubleshooting](#flutter-troubleshooting)
- [Contributing](#contributing)

---

## Laravel Integration

This section explains how to set up and use the Laravel package to send notifications.

### Laravel Prerequisites

- **Laravel Version:** 8+
- **Firebase Project:** Create one in the [Firebase Console](https://console.firebase.google.com/)
- **Service Account Key:** Download the JSON file from your Firebase project settings

### Laravel Installation

1. **Install via Composer:**
   ```bash
   composer require sendfirebase/notificationphp
   ```

2. **Publish the Configuration:**
   ```bash
   php artisan vendor:publish --provider="SendFireBaseNotificationPHP\Providers\FireBaseNotificationServiceProvider" --tag="config"
   ```

3. **Configure Your Environment:**

   Add these lines to your `.env` file:
   ```env
   FIREBASE_PROJECT_ID=your-project-id
   FIREBASE_API_VERSION=v1
   ```

4. **Store Firebase Credentials:**

   Create a directory and move your service account JSON file:
   ```bash
   mkdir -p storage/app/firebase
   mv path/to/your-service-account.json storage/app/firebase/firebase_credentials.json
   ```

### Laravel Configuration

Ensure your `config/firebase.php` file contains:
```php
return [
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'version' => env('FIREBASE_API_VERSION', 'v1'),
    'credentials_file' => storage_path('app/firebase/firebase_credentials.json'),
];
```

### Laravel Usage Examples

**1. Send a Notification to a Single User:**
```php
use App\Models\User;
use SendFireBaseNotificationPHP\FirebaseNotificationService;

public function notifyUser(Request $request)
{
    $firebaseService = new FirebaseNotificationService();
    
    $response = $firebaseService->sendNotificationToSingle(
        new User(),          // User model instance
        $request->user_id,   // Target user ID
        "New Message",       // Notification title
        "You have a new notification!", // Notification body
        'fcm_token'          // Column name where FCM token is stored
    );

    return response()->json($response);
}
```

**2. Broadcast a Notification to All Users:**
```php
$firebaseService->sendNotificationToAll(
    new User(),           // User model instance
    "Global Alert",       // Notification title
    "Important system update!", // Notification body
    'fcm_token'           // Column name for FCM token
);
```

**3. Send a Notification to a Topic:**
```php
$firebaseService->sendNotificationToTopic(
    "all_users",          // Topic name
    "Topic Update",       // Notification title
    "New content available!" // Notification body
);
```

### Laravel Troubleshooting

- **Missing Credentials:**  
  Verify that the service account JSON is located in `storage/app/firebase` and that your Firebase project ID in the `.env` file matches your Firebase credentials.

- **General Issues:**  
  Check your Laravel logs for errors and review the configuration in `config/firebase.php`.

---

## Flutter Integration

This section details the steps for setting up your Flutter application to receive and handle Firebase notifications.

### Flutter Prerequisites

- **Flutter Version:** 3.0+
- **Firebase Project:** Ensure your Firebase project is configured for both Android and iOS  
- **Platform-Specific Setup:** For iOS, follow the [Firebase iOS Setup](https://firebase.google.com/docs/ios/setup)

### Flutter Setup & Dependencies

1. **Add Dependencies:**

   Add the following dependencies to your `pubspec.yaml`:
   ```yaml
   dependencies:
     firebase_messaging: ^15.2.1
     flutter_local_notifications: ^18.0.1
   ```

2. **Basic App Initialization:**

   In your `main.dart`, initialize notifications before running the app:
   ```dart
   void main() async {
     WidgetsFlutterBinding.ensureInitialized();
     await FbNotifications.initNotifications();
     runApp(MyApp());
   }
   ```

### Flutter Notification Initialization and Handling

Implement a mixin to centralize notification setup and handling:

```dart
mixin FbNotifications on State<MyApp> {
  static late AndroidNotificationChannel channel;
  static late FlutterLocalNotificationsPlugin localNotificationsPlugin;

  // Background Handler
  static Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
    print("Handling background message: ${message.messageId}");
  }

  // Initialization: Set up channels and permissions
  static Future<void> initNotifications() async {
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
    
    // Android Channel Setup
    channel = const AndroidNotificationChannel(
      'high_importance_channel',
      'Important Notifications',
      importance: Importance.high,
      playSound: true,
    );

    localNotificationsPlugin = FlutterLocalNotificationsPlugin();
    await localNotificationsPlugin
        .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
        ?.createNotificationChannel(channel);

    // iOS Notification Presentation Options
    await FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(
      alert: true,
      badge: true,
      sound: true,
    );
  }

  // Request Notification Permissions (especially for iOS)
  Future<void> requestNotificationPermissions() async {
    final settings = await FirebaseMessaging.instance.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
    
    if (settings.authorizationStatus == AuthorizationStatus.authorized) {
      print('Notifications granted');
    }
  }

  // Foreground Notification Handling for Android
  void initializeForegroundNotificationForAndroid() {
    FirebaseMessaging.onMessage.listen((RemoteMessage message) {
      RemoteNotification? notification = message.notification;
      AndroidNotification? android = notification?.android;

      if (notification != null && android != null) {
        localNotificationsPlugin.show(
          notification.hashCode,
          notification.title,
          notification.body,
          NotificationDetails(
            android: AndroidNotificationDetails(
              channel.id,
              channel.name,
              icon: '@mipmap/ic_launcher',
            ),
          ),
        );
      }
    });
  }
}
```

3. **Platform-Specific Configurations:**

   - **Android:**  
     Update your `AndroidManifest.xml` to include:
     ```xml
     <application ...>
       <meta-data
         android:name="com.google.firebase.messaging.default_notification_channel_id"
         android:value="high_importance_channel"/>
     </application>
     ```

   - **iOS:**  
     Follow the official Firebase iOS setup guide and ensure push notifications are enabled in Xcode.

### Flutter Usage Examples

**Subscribing to a Topic:**
```dart
FirebaseMessaging.instance.subscribeToTopic("all_users");
```

**Handling Background Messages:**
```dart
FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
```

**Handling Notification Taps (when the app is opened via a notification):**
```dart
FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
  Navigator.pushNamed(context, '/message');
});
```

**Listening for Token Refresh:**
```dart
FirebaseMessaging.instance.onTokenRefresh.listen((newToken) {
  // Update your server with the new token
});
```

### Flutter Troubleshooting

- **iOS Notifications Not Showing:**  
  Make sure you have completed the iOS setup in Xcode, requested user permissions, and enabled push notifications in your Apple Developer account.

- **Android Notifications Silent:**  
  Confirm that the notification channel’s importance is set to high, and verify the metadata in `AndroidManifest.xml`.

- **Token Issues:**  
  Ensure that you handle token refresh correctly to keep the server updated with the latest token.

---

## Contributing

We welcome your contributions to improve the project for both Laravel and Flutter users!

1. **Report Issues:**  
   Use GitHub Issues to report bugs or request new features.

2. **Development Setup:**
   ```bash
   git clone https://github.com/YacoubAl-hardari/firebasenotificationphp.git
   cd firebasenotificationphp
   composer install
   ```

3. **Testing:**
   Create tests in the `tests/` directory and run:
   ```bash
   php artisan test
   ```

4. **Coding Standards:**  
   Follow the PSR-12 coding style, use PHPStan (level 6), and include PHPDoc comments.

5. **Pull Requests:**  
   Fork the repository, create feature branches, and submit a PR with a detailed description of your changes.


