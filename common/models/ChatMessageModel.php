<?php

namespace common\models;

use common\models\redis\ChatMessageQueueStorage;
use yii\base\BaseObject;

class ChatMessageModel extends BaseObject
{
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 0;

    /** @var ChatMessageQueueStorage */
    protected $model;

    public function init()
    {
        parent::init();
        $this->model = new ChatMessageQueueStorage();
    }

    public function getList($chatId, $offset = 0, $limit = 10): array
    {
        return $this->model->getOffsetList($chatId, $offset, $limit);
    }

    public function saveMessageToChat(int $chatId, int $userId, string $message)
    {
        return $this->model->addToTail(
            $chatId,
            json_encode([
                'u' => $userId,
                'm' => $message,
                'd' => time(),
                's' => self::STATUS_ACTIVE,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }
}
