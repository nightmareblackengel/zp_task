<?php

namespace common\ext\base;

use common\ext\traits\ErrorTrait;
use common\ext\patterns\Singleton;
use Exception;
use Yii;
use yii\base\BaseObject;
use yii\db\Connection;
use yii\db\Query;

abstract class MySqlModel extends BaseObject
{
    use Singleton;
    use ErrorTrait;

    const DEFAULT_ERR_ATTRIBUTE = '-';

    abstract public static function tableName(): string;

    public static function getDb(): Connection
    {
        return Yii::$app->getDb();
    }

    public function getDefaultError()
    {
        if (empty($this->_errors[self::DEFAULT_ERR_ATTRIBUTE])) {
            return '';
        }
        return array_shift($this->_errors[self::DEFAULT_ERR_ATTRIBUTE]);
    }

    public function getItemBy(array $whereParams, string $select = '*'): ?array
    {
        if (empty($whereParams) || empty($select)) {
            return null;
        }

        $query = new Query();
        $query->select($select)
            ->from(static::tableName())
            ->where($whereParams)
            ->all();

        try {
            $result = $query->one();
        } catch (Exception $ex) {
            $this->addError(self::DEFAULT_ERR_ATTRIBUTE, 'Ошибка БД:' . $ex->getMessage());
        }
        if (empty($result)) {
            return null;
        }

        return $result;
    }

    public function insertBy(array $params): ?int
    {
        $this->clearErrors();
        if (empty($params)) {
            return null;
        }

        $insertRes = static::getDb()
            ->createCommand()
            ->insert(static::tableName(), $params)
            ->execute();
        if (empty($insertRes)) {
            return null;
        }

        return static::getDb()->lastInsertID;
    }

    public function updateBy(array $values, array $whereCond): ?int
    {
        $this->clearErrors();
        if (empty($values) || empty($whereCond)) {
            return null;
        }

        try {
            $updateRes = static::getDb()
                ->createCommand()
                ->update(static::tableName(), $values, $whereCond)
                ->execute();
        } catch (Exception $ex) {
            $this->addError(self::DEFAULT_ERR_ATTRIBUTE, 'Ошибка! При обновлении данных в БД:' . $ex->getMessage());
        }

        return !empty($updateRes) ? $updateRes :  null;
    }

    public function getList(array $whereParams = [], $select = '*', ?int $offset = null, ?int $limit = null): ?array
    {
        $query = new Query();

        try {
            $list = $query->select($select)
                ->from(static::tableName())
                ->andWhere($whereParams)
                ->offset($offset)
                ->limit($limit)
                ->all();
        } catch (Exception $ex) {
        }
        if (empty($list)) {
            return [];
        }

        return $list;
    }
}
