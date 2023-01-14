<?php

namespace common\models;

use common\ext\patterns\Singleton;
use common\models\redis\ChatMessageQueueStorage;
use frontend\models\forms\ChatMessageForm;
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

    public function getList($chatId, $offset = 0, $limit = 10): array
    {
        return $this->model->getOffsetList($chatId, $offset, $limit);
    }

    public function saveMessageFrom(ChatMessageForm $form): bool
    {
        return (bool) $this->model->addToTail(
            $form->chatId,
            json_encode([
                'u' => $form->userId,
                'm' => $form->message,
                't' => $form->messageType,
                's' => self::STATUS_ACTIVE,
                'd' => time(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }
}
