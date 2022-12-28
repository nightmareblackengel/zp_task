<?php

namespace common\models\mysql;

use common\ext\base\MySqlModel;
use yii\db\Connection;

class UserModel extends MySqlModel
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    public static function tableName(): string
    {
        return '`user`';
    }

    public function getItemById(int $id, string $selectedFields = '*'): ?array
    {
        return static::getDb()
            ->createCommand(sprintf("SELECT %s FROM %s WHERE `id`='%d'", $selectedFields, static::tableName(), $id))
            ->queryOne() ?: null;
    }

    public function getItemByEmail(string $email, string $selectedFields = '*'): ?array
    {
        return static::getDb()
            ->createCommand(sprintf("SELECT %s FROM %s WHERE `email`='%s'", $selectedFields, static::tableName(), $email))
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
            static::tableName()
        );
        $selectRes = static::getDb()
            ->createCommand($selectQueryStr, $params)
            ->queryAll();
        if (empty($selectRes)) {
            return [];
        }

        return array_column($selectRes, 'name', 'id');
    }
}
