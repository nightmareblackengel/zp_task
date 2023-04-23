<?php

namespace common\models;

use common\ext\patterns\Singleton;
use common\models\redis\ChatDateTimeMhashStorage;
use common\models\redis\ChatMessageQueueStorage;
use yii\base\BaseObject;

class ChatMessageModel extends BaseObject
{
    use Singleton;

    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    const MESSAGE_TYPE_SIMPLE = 1;
    const MESSAGE_TYPE_SYSTEM = 2;

    /** @var ChatMessageQueueStorage */
    protected $model;

    public function init()
    {
        parent::init();
        $this->model = ChatMessageQueueStorage::getInstance();
    }

    public function insertMessage(int $userId, int $chatId, string $message, int $messageType, $date = null): bool
    {
        if (empty($date)) {
            $date = microtime(true);
        }
        ChatDateTimeMhashStorage::getInstance()
            ->setValue(null, $chatId, time());

        $msgSaveRes = (bool) $this->model
            ->addToTail(
                $chatId,
                json_encode([
                    'u' => $userId,
                    'm' => $message,
                    't' => $messageType,
                    's' => self::STATUS_ACTIVE,
                    'd' => $date,
                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            );

        return $msgSaveRes;
    }
}
