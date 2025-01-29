<?php

namespace SendFireBaseNotificationPHP\Repositories;

use Illuminate\Database\Eloquent\Model;

class FirebaseNotificationRepository
{
    /**
     * Get all users with valid FCM tokens from a given model.
     *
     * @param Model $model
     * @param string $tokenColumn
     * @return \Illuminate\Support\Collection
     */
    public function getAllUsersWithTokens(Model $model, $tokenColumn = 'fcm_token')
    {
        return $model->newQuery()
            ->select($tokenColumn)
            ->whereNotNull($tokenColumn)
            ->where($tokenColumn, '!=', '')
            ->get();
    }

    /**
     * Get a single user with a valid FCM token by ID from a given model.
     *
     * @param Model $model
     * @param int $id
     * @param string $tokenColumn
     * @return Model|null
     */
    public function getUserWithTokenById(Model $model, $id, $tokenColumn = 'fcm_token')
    {
        return $model->newQuery()
            ->select($tokenColumn)
            ->where('id', $id)
            ->whereNotNull($tokenColumn)
            ->where($tokenColumn, '!=', '')
            ->first();
    }
}
