<?php

namespace common\models;

use common\ext\patterns\Singleton;
use common\models\redis\ChatMessageQueueStorage;
use common\models\redis\SysMsgCountHashStorage;
use Exception;
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
        if ($form->message[0] === '/') {
            $form->messageType = self::MESSAGE_TYPE_SYSTEM;
        }

        $msgSaveRes = (bool) $this->model->addToTail(
            $form->chatId,
            json_encode([
                'u' => $form->userId,
                'm' => $form->message,
                't' => $form->messageType,
                's' => self::STATUS_ACTIVE,
                'd' => microtime(true),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );

        if ($form->messageType === self::MESSAGE_TYPE_SYSTEM) {
            SysMsgCountHashStorage::getInstance()->increment($form->userId, $form->chatId);
        }

        return $msgSaveRes;
    }

    public function getChatListMsgCount(array $chatIds): array
    {
        if (empty($chatIds)) {
            return [];
        }
        // prepare transaction for each 'chatId'
        $prepareTransList = [];
        try {
            $this->model::getStorage()->multi();

            foreach ($chatIds as $numInd => $chatId) {
                $prepareTransList[$numInd] = $this->model::getInstance()->getQueueLength($chatId);
            }

            $chatCountList = $this->model::getStorage()->exec();
        } catch (Exception $ex) {
            $this->model::getStorage()->discard();
        }

        if (empty($chatCountList)) {
            return [];
        }
        // fill result
        $result = [];
        foreach ($prepareTransList as $numInd => $transRes) {
            $chatCount = 0;
            if ($transRes === $this->model::TRANSACTION_QUEUED) {
                $chatCount = (int) $chatCountList[$numInd];
            }
            $result[$chatIds[$numInd]] = $chatCount;

        }

        return $result;
    }
}
