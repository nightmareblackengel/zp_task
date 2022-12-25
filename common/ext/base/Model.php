<?php

namespace common\ext\base;

use common\ext\patterns\Singleton;
use Exception;
use Yii;
use yii\db\Connection;

class Model extends \yii\base\Model
{
    use Singleton;

    public $model;

    protected function prepareInsertStr(string $tableName, array $params): array
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

        $insertStr = "INSERT INTO `" . $tableName . '`'
            . "(" . implode(',', $colNames) . ") "
            . "VALUES (" . implode(',', $colValues) . ")";

        return [$insertStr, $colParams];
    }

    public function prepareUpdateStr(string $tableName, array $values, array $whereCond): array
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

        $updateStr = "UPDATE `$tableName` SET "
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
        if (empty($params)) {
            return null;
        }

        list($insertStr, $insertParams) = $this->prepareInsertStr($this->model::tableName(), $params);
        if (empty($insertStr)) {
            return null;
        }
        /** @var Connection $db */
        $db = $this->model::getDb();
        try {
            $insertRes = $db->createCommand($insertStr, $insertParams)->execute();
        } catch (Exception $ex) {
        }
        if (empty($insertRes)) {
            return null;
        }

        return $db->lastInsertID;
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
            $this->model::tableName()
        );
        /** @var Connection $db */
        $db = $this->model::getDb();
        try {
            $selectRes = $db->createCommand($selectQueryStr, $colParams)->queryOne();
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
        if (empty($values) || empty($whereCond)) {
            return null;
        }

        list($updateStr, $params) = $this->prepareUpdateStr($this->model::tableName(), $values, $whereCond);
        if (empty($updateStr)) {
            return null;
        }
        /** @var Connection $db */
        $db = $this->model::getDb();
        try {
            $updateRes = $db->createCommand($updateStr, $params)->execute();
        } catch (Exception $ex) {
            echo $ex->getMessage();
            Yii::$app->end();
            exit();
        }

        return !empty($updateRes) ? $updateRes :  null;
    }
}
