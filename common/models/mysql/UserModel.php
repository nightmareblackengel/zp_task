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

    public function getItemByEmail(string $email): ?array
    {
        $query = new Query();
        $query->from(self::tableName())
            ->where(['[[email]]' => $email]);

        return $query->one();
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
            ->where(['NOT IN', '[[id]]', $exceptIds])
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

    public function getUserListForChat(?int $chatId, bool $withBannedInfo = false): array
    {
        if (empty($chatId)) {
            return [];
        }
        $query = new Query();
        $query->select(
            [
                'u.`id`',
                static::getUserNameQuery(),
            ]
        );
        if ($withBannedInfo) {
            $query->addSelect(['uc.isUserBanned']);
        }
        $query->from(['uc' => UserChatModel::tableName()])
            ->innerJoin(['u' => UserModel::tableName()], 'u.`id` = uc.`userId`')
            ->where(['uc.`chatId`' => $chatId]);

        $users = $query->all();
        if (empty($users)) {
            return [];
        }
        if (!$withBannedInfo) {
            return array_column($users, 'name', 'id');
        }

        return array_column($users, null, 'id');
    }

    public static function getUserNameQuery(string $resFieldName = 'name'): string
    {
        return sprintf("CONCAT(IFNULL(`name`, ''), '(', `email`, ')') AS %s", $resFieldName);
    }
}
