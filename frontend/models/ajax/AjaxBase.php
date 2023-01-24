<?php

namespace frontend\models\ajax;

use frontend\models\helpers\AjaxHelper;
use yii\base\BaseObject;

abstract class AjaxBase extends BaseObject
{
    protected ?int $showInResponse = AjaxHelper::AJAX_REQUEST_EXCLUDE;

    abstract public function load(?array $data): bool;

    abstract public function prepareResponse(?int $userId, ?int $chatId): ?array;
}
