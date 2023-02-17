<?php

namespace common\models;

use common\ext\patterns\Singleton;
use common\models\redis\ChatMessageQueueStorage;
use common\models\redis\SysMsgCountStringStorage;
use Exception;
use frontend\models\forms\MessageAddForm;
use frontend\models\helpers\MessageCommandHelper;
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

    public function saveMessageFrom(MessageAddForm $form): bool
    {
        if ($form->message[0] === '/') {
            $form->messageType = self::MESSAGE_TYPE_SYSTEM;
            // доп. обработка тех сообщений, которые нужно выполнить
            if (mb_strpos($form->message, MessageCommandHelper::MSG_CHAT_CMD_CLEAR_HISTORY) === 0) {
                $this->executeClearhistoryCmd($form);
                return true;
            }
        }

        return $this->saveMessage($form);
    }

    public function getChatListMsgCount(array $chatIds): array
    {
        if (empty($chatIds)) {
            return [];
        }
        // prepare transaction for each 'chatId'
        $chatsMsgCountList = [];
        $chatSysMsgCountList = [];
        try {
            $this->model::getStorage()->multi();

            foreach ($chatIds as $numInd => $chatId) {
                $chatsMsgCountList[$numInd] = $this->model::getInstance()->getQueueLength($chatId);
                $chatSysMsgCountList[$numInd] = SysMsgCountStringStorage::getInstance()->getValue($chatId);
            }

            $chatCountList = $this->model::getStorage()->exec();
        } catch (Exception $ex) {
            $this->model::getStorage()->discard();
        }

        if (empty($chatCountList)) {
            return [];
        }

        $result = [];
        foreach ($chatsMsgCountList as $numInd => $transRes) {
            $chatCount = 0;
            // получим общее кол-во сообщений
            if ($transRes === $this->model::TRANSACTION_QUEUED) {
                $chatCount = (int) $chatCountList[$numInd];
            }
            // получим кол-во системных сообщений
            if (!empty($chatSysMsgCountList[$numInd])) {
                $chatCount -= (int) $chatSysMsgCountList[$numInd];
            }
            $result[$chatIds[$numInd]] = $chatCount;
        }

        return $result;
    }

    public function executeClearhistoryCmd(MessageAddForm $form): bool
    {
        $this->model->delete($form->chatId);
        $form->message = 'История чата была успешно удалена в ' . date('Y-m-d H:i:s');
        // удалим значение "количество системных комманд"
        SysMsgCountStringStorage::getInstance()->delete($form->chatId);
        // сохраним сообщение
        return $this->saveMessage($form);
    }

    protected function saveMessage(MessageAddForm $form): bool
    {
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
            SysMsgCountStringStorage::getInstance()->increment($form->chatId);
        }

        return $msgSaveRes;
    }
}
