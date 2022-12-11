<?php

namespace common\models;

use common\ext\Model;
use common\models\mysql\User;

class UserModel extends Model
{
    /** @var User */
    public $model = User::class;

    public function getFullItem(int $id, $selectedFields = []): ?array
    {
        if (empty($selectedFields)) {
            $select = '*';
        } else {
            $select = implode(',', $selectedFields);
        }

        return $this->model::getDb()
            ->createCommand(sprintf("SELECT %s FROM %s WHERE `id`=%d", $select, $this->model::tableName(), $id))
            ->queryOne() ?: null;
    }
}
