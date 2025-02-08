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

    protected function getAccessToken(): string
    {
        $client = new GoogleClient();
        $client->setAuthConfig($this->credentialsFile);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();

        $accessToken = $client->getAccessToken();

        if (!isset($accessToken['access_token'])) {
            throw new \Exception('Failed to retrieve Firebase access token.');
        }

        return $accessToken['access_token'];
    }

    protected function sendFirebaseRequest(array $headers, array $payload)
    {
        $response = Http::withHeaders($headers)
            ->post($this->fcmUrl, $payload);

        if ($response->failed()) {
            throw new \Exception("Firebase request failed: " . $response->body());
        }

        return $response->json();
    }

    public function sendNotification(string $deviceToken, string $title, string $body)
    {
        $accessToken = $this->getAccessToken();

        $headers = [
            "Authorization" => "Bearer $accessToken",
            "Content-Type" => "application/json",
        ];

        $payload = [
            "message" => [
                "token" => $deviceToken,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
            ],
        ];

        return $this->sendFirebaseRequest($headers, $payload);
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
        $user = $this->repository->getUserWithTokenById($model, $id, $tokenColumn);

        if (!$user || empty($user->$tokenColumn)) {
            return ['success' => false, 'message' => 'User with valid FCM token not found.'];
        }

        $response = $this->sendNotification($user->$tokenColumn, $title, $body);

        return ['success' => true, 'message' => 'Notification sent to the user.', 'response' => $response];
    }

    public function sendNotificationToTopic(string $topic, string $title, string $body)
    {
        $accessToken = $this->getAccessToken();

        $headers = [
            "Authorization" => "Bearer $accessToken",
            "Content-Type" => "application/json",
        ];

        $payload = [
            "message" => [
                "topic" => $topic,
                "notification" => [
                    "title" => $title,
                    "body" => $body,
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "sound" => "default",
                        ],
                    ],
                ],
            ],
        ];

        return $this->sendFirebaseRequest($headers, $payload);
    }
}
