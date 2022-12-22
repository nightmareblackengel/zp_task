<?php

namespace common\ext\base;

use common\ext\patterns\Singleton;
use Exception;
use yii\db\Connection;

class Model extends \yii\base\Model
{
    use Singleton;

    public $model;

    protected function prepareKeyValues(array $params): array
    {
        if (empty($params)) {
            return [null, null, null];
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

        return [$colNames, $colValues, $colParams];
    }

    public function insertBy(array $params): ?int
    {
        if (empty($params)) {
            return null;
        }

        list($colNames, $colValues, $colParams) = $this->prepareKeyValues($params);
        if (empty($colNames)) {
            return null;
        }

        $insertQueryStr = sprintf(
            "INSERT INTO %s "
            . "(" . implode(',', $colNames) . ") "
            . "VALUES(" . implode(',', $colValues) . ")",
            $this->model::tableName()
        );
        /** @var Connection $db */
        $db = $this->model::getDb();
        try {
            $insertRes = $db->createCommand($insertQueryStr, $colParams)->execute();
        } catch (Exception $ex) {
        }
        if (empty($insertRes)) {
            return null;
        }

        return $db->lastInsertID;
    }
}
