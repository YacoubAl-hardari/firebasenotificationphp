<?php

namespace SendFireBaseNotificationPHP\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;
use SendFireBaseNotificationPHP\Repositories\FirebaseNotificationRepository;

class FirebaseNotificationService
{
    protected $fcmUrl;
    protected $projectId;
    protected $credentialsFile;
    protected $repository;

    public function __construct(FirebaseNotificationRepository $repository)
    {
        $this->projectId = config('firebase.project_id');
        $this->fcmUrl = "https://fcm.googleapis.com/" . config('firebase.version') . "/projects/{$this->projectId}/messages:send";
        $this->credentialsFile = config('firebase.credentials_file');
        $this->repository = $repository;
    }

    public function sendNotification(string $deviceToken, string $title, string $body)
    {
        // Initialize Google Client
        $client = new GoogleClient();
        $client->setAuthConfig($this->credentialsFile);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();

        // Get access token
        $accessToken = $client->getAccessToken()['access_token'];

        // Prepare headers
        $headers = [
            "Authorization: Bearer $accessToken",
            'Content-Type: application/json',
        ];

        // Prepare payload
        $payload = [
            "message" => [
                "token" => $deviceToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
            ],
        ];

        // Send notification via CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception("CURL Error: $error");
        }

        return json_decode($response, true);
    }

    public function sendNotificationToAll(Model $model, string $title, string $body, string $tokenColumn = 'fcm_token')
    {
        $users = $this->repository->getAllUsersWithTokens($model, $tokenColumn);

        if ($users->isEmpty()) {
            return ['success' => false, 'message' => 'No users with valid FCM tokens found.'];
        }

        foreach ($users as $user) {
            if (!empty($user->$tokenColumn)) {
                $this->sendNotification($user->$tokenColumn, $title, $body);
            }
        }

        return ['success' => true, 'message' => 'Notifications sent to all users.'];
    }

    public function sendNotificationToSingle(Model $model, int $id, string $title, string $body, string $tokenColumn = 'fcm_token')
    {
        if (is_null($id)) {
            return ['success' => false, 'message' => 'Invalid user ID provided.'];
        }

        $user = $this->repository->getUserWithTokenById($model, $id, $tokenColumn);

        if (!$user || empty($user->$tokenColumn)) {
            return ['success' => false, 'message' => 'User with valid FCM token not found.'];
        }

        $response = $this->sendNotification($user->$tokenColumn, $title, $body);

        return ['success' => true, 'message' => 'Notification sent to the user.', 'response' => $response];
    }
}
