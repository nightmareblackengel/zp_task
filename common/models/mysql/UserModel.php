<?php

namespace common\models\mysql;

use common\ext\base\MySqlModel;
use yii\db\Query;

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

    public function getExceptList(array $exceptIds, string $namePart, int $limit = 20): array {
        if (empty($exceptIds)) {
            return [];
        }

        $query = new Query();
        $query->select([
                '[[id]]',
                static::getUserNameQuery('text')
            ])
            ->from(static::tableName())
            ->where(['NOT IN', 'id', $exceptIds])
            ->andWhere([
                '[[status]]' => self::STATUS_ENABLED,
            ])
            ->andWhere([
                'OR',
                ['LIKE', '[[name]]', $namePart],
                ['LIKE', '[[email]]', $namePart]
            ])
            ->limit($limit);

        $selectRes = $query->all();
        if (empty($selectRes)) {
            return [];
        }

        return $selectRes;
    }

    public function getUserListForChat(?int $chatId): array
    {
        if (empty($chatId)) {
            return [];
        }
        $query = sprintf(
            "SELECT u.`id`, " . static::getUserNameQuery() . " "
            . "FROM %s uc "
            . "INNER JOIN %s u ON u.`id` = uc.`userId` "
            . "WHERE uc.`chatId` = :chatId",
            UserChatModel::tableName(),
            static::tableName()
        );

        $users = static::getDb()
            ->createCommand($query, [
                ':chatId' => $chatId,
            ])
            ->queryAll();

        return array_column($users, 'name', 'id');
    }

    public function getUserListForAddToChannel(?int $chatId): array
    {
        if (empty($chatId)) {
            return [];
        }

        $query = sprintf("
            SELECT u.`id`, " . static::getUserNameQuery() . "
            FROM %s u
            WHERE 1=1
            AND u.`status` = :status
            AND u.`id` NOT IN (
                SELECT uc1.`userId`
                FROM %s uc1
                WHERE uc1.`chatId`  = :chatId
            )",
            self::tableName(),
            UserChatModel::tableName()
        );

        $users = static::getDb()
            ->createCommand($query, [
                'chatId' => $chatId,
                'status' => self::STATUS_ENABLED,
            ])
            ->queryAll();

        return array_column($users, 'name', 'id');
    }

    public static function getUserNameQuery(string $resFieldName = 'name'): string
    {
        return sprintf("CONCAT(IFNULL(`name`, ''), '(', `email`, ')') as %s", $resFieldName);
    }
}
