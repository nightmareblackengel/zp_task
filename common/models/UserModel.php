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

    public function getFullItem(int $id, $selectedFields = []): ?array
    {
        $select = $this->getSelectedFields($selectedFields);

        return $this->model::getDb()
            ->createCommand(sprintf("SELECT %s FROM %s WHERE `id`='%d'", $select, $this->model::tableName(), $id))
            ->queryOne() ?: null;
    }

    public function getItemBy(string $email, $selectedFields = []): ?array
    {
        $select = $this->getSelectedFields($selectedFields);

        return $this->model::getDb()
            ->createCommand(sprintf("SELECT %s FROM %s WHERE `email`='%s'", $select, $this->model::tableName(), $email))
            ->queryOne() ?: null;
    }

    protected function getSelectedFields($selectedFields = []): string
    {
        if (empty($selectedFields)) {
            $select = '*';
        } else {
            $select = implode(',', $selectedFields);
        }

        return $select;
    }
}
