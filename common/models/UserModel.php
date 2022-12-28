<?php

namespace common\models;

use common\ext\base\Model;
use common\models\mysql\User;
use Exception;
use Yii;
use yii\db\Connection;

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

    public function getShortListExcept(int $exceptUserId): array
    {
        if (empty($exceptUserId)) {
            return [];
        }

        $params = [
            ':pid' => $exceptUserId,
            ':status' => self::STATUS_ENABLED,
        ];

        $selectQueryStr = sprintf(
            "SELECT `id`, CONCAT(`name`, '(', `email`, ')') as name FROM %s "
            . " WHERE `id` <> :pid AND `status` = :status",
            $this->model::tableName()
        );
        /** @var Connection $db */
        $db = $this->model::getDb();
        $selectRes = $db->createCommand($selectQueryStr, $params)->queryAll();
        if (empty($selectRes)) {
            return [];
        }

        return array_column($selectRes, 'name', 'id');
    }
}
