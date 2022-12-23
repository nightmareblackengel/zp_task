<?php

namespace common\models;

use common\ext\base\Model;
use common\models\mysql\User;

class UserModel extends Model
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /** @var User */
    public $model = User::class;

    public function getItemById(int $id, string $selectedFields = '*'): ?array
    {
        return $this->model::getDb()
            ->createCommand(sprintf("SELECT %s FROM %s WHERE `id`='%d'", $selectedFields, $this->model::tableName(), $id))
            ->queryOne() ?: null;
    }

    public function getItemByEmail(string $email, string $selectedFields = '*'): ?array
    {
        return $this->model::getDb()
            ->createCommand(sprintf("SELECT %s FROM %s WHERE `email`='%s'", $selectedFields, $this->model::tableName(), $email))
            ->queryOne() ?: null;
    }
}
