<?php

namespace common\ext\base;

use common\ext\patterns\Singleton;
use Exception;
use Yii;
use yii\base\BaseObject;
use yii\db\Connection;

abstract class MySqlModel extends BaseObject
{
    use Singleton;

    public array $errors = [];

    abstract public static function tableName(): string;

    public static function getDb(): Connection
    {
        return Yii::$app->getDb();
    }

    protected function prepareInsertStr(array $params): array
    {
        if (empty($params)) {
            return [null, null];
        }
        $colNames = [];
        $colValues = [];
        $colParams = [];
        foreach ($params as $columnName => $columnValue) {
            $colNames[] = "`$columnName`";
            $valueParam = ":$columnName";
            $colValues[] = $valueParam;
            $colParams[$valueParam] = $columnValue;
        }

        $insertStr = "INSERT INTO " . static::tableName()
            . "(" . implode(',', $colNames) . ") "
            . "VALUES (" . implode(',', $colValues) . ")";

        return [$insertStr, $colParams];
    }

    public function prepareUpdateStr(array $values, array $whereCond): array
    {
        if (empty($values) || empty($whereCond)) {
            return [null, null];
        }
        $colNames = [];
        $colParams = [];
        foreach ($values as $columnName => $columnValue) {
            $valueParam = ":$columnName";
            $colNames[] = "`$columnName` = $valueParam";
            $colParams[$valueParam] = $columnValue;
        }

        $whereColumns = [];
        foreach ($whereCond as $columnName => $columnValue) {
            $valueParam = ":$columnName";
            $whereColumns[] = "`$columnName` = $valueParam";
            $colParams[$valueParam] = $columnValue;
        }

        $updateStr = "UPDATE " . static::tableName() . " SET "
            . implode(', ', $colNames)
            . " WHERE " . implode(' AND ', $whereColumns);

        return [$updateStr, $colParams];
    }

    public function prepareWhereStr(?array $params): array
    {
        if (empty($params)) {
            return [null, null];
        }

        $where = [];
        $columnParams = [];
        foreach ($params as $colName => $colValue) {
            $paramName = ":$colName";
            $where[] = '`' . $colName . '`=' . $paramName;
            $columnParams[$paramName] = $colValue;
        }

        return [
            implode(' AND ', $where),
            $columnParams
        ];
    }

    public function insertBy(array $params): ?int
    {
        $this->errors = [];
        if (empty($params)) {
            return null;
        }

        list($insertStr, $insertParams) = $this->prepareInsertStr($params);
        if (empty($insertStr)) {
            return null;
        }
        try {
            $insertRes = static::getDb()
                ->createCommand($insertStr, $insertParams)
                ->execute();
        } catch (Exception $ex) {
            $this->errors[] = 'Ошибка! При вставке данных в БД';
        }
        if (empty($insertRes)) {
            return null;
        }

        return static::getDb()->lastInsertID;
    }

    public function getItemBy(array $whereParams, string $select = '*'): ?array
    {
        if (empty($whereParams) || empty($select)) {
            return null;
        }

        list($where, $colParams) = $this->prepareWhereStr($whereParams);
        if (empty($where)) {
            return null;
        }

        $selectQueryStr = sprintf(
            "SELECT %s FROM %s "
            . " WHERE " . $where,
            $select,
            static::tableName()
        );
        try {
            $selectRes = static::getDb()
                ->createCommand($selectQueryStr, $colParams)
                ->queryOne();
        } catch (Exception $ex) {
//            echo $ex->getMessage();
//            Yii::$app->end();
//            exit();
        }
        if (empty($selectRes)) {
            return null;
        }

        return $selectRes;
    }

    public function updateBy(array $values, array $whereCond): ?int
    {
        $this->errors = [];
        if (empty($values) || empty($whereCond)) {
            return null;
        }

        list($updateStr, $params) = $this->prepareUpdateStr($values, $whereCond);
        if (empty($updateStr)) {
            return null;
        }
        try {
            $updateRes = static::getDb()
                ->createCommand($updateStr, $params)
                ->execute();
        } catch (Exception $ex) {
            $this->errors[] = 'Ошибка! При обновлении данных в БД';
        }

        return !empty($updateRes) ? $updateRes :  null;
    }
}