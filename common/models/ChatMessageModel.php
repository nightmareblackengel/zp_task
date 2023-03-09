<?php

namespace common\models;

use common\ext\patterns\Singleton;
use common\ext\redis\RedisBase;
use common\models\mysql\UserChatModel;
use common\models\mysql\UserModel;
use common\models\redis\ChatMessageQueueStorage;
use common\models\redis\DelayMsgSortedSetStorage;
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

    public function getList($chatId, $startPosition = 0, $endPosition = -1): array
    {
        return $this->model->getList($chatId, $startPosition, $endPosition);
    }

    public function saveMessageFrom(MessageAddForm $form): bool
    {
        if ($form->message[0] === '/') {
            $cmdList = explode(' ', $form->message);
            $form->messageType = self::MESSAGE_TYPE_SYSTEM;
            // доп. обработка тех сообщений, которые нужно выполнить
            if ($cmdList[0] === MessageCommandHelper::MSG_CHAT_CMD_CLEAR_HISTORY) {
                $this->executeClearHistoryCmd($form);
                return true;
            } elseif ($cmdList[0] === MessageCommandHelper::MSG_CHAT_CMD_KICK && count($cmdList)) {
                $this->executeKickByEmail($cmdList[1], $form->chatId);
                return true;
            } elseif ($cmdList[0] === MessageCommandHelper::MSG_CMD_SEND_MSG_WITH_DELAY) {
                $this->executeSendMsgWithDelay($form, $cmdList);
                return true;
            }
        }

        return $this->insertMessage($form->userId, $form->chatId, $form->message, $form->messageType);
    }

    public function insertMessage(int $userId, int $chatId, string $message, int $messageType, $date = null): bool
    {
        if (empty($date)) {
            $date = microtime(true);
        }

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
        if ($messageType === self::MESSAGE_TYPE_SYSTEM) {
            SysMsgCountStringStorage::getInstance()->increment($chatId);
        }

        return $msgSaveRes;
    }

    public function getChatListMsgCount(array &$chatIds): array
    {
        if (empty($chatIds)) {
            return [];
        }
        // prepare transaction for each 'chatId'
        $queuedMsgCountList = [];
        $queuedSysMsgCountList = [];
        try {
            $this->model::getStorage()->multi();
            SysMsgCountStringStorage::getStorage()->multi();

            foreach ($chatIds as $numInd => $chatId) {
                $queuedMsgCountList[$numInd] = $this->model::getInstance()->getQueueLength($chatId);
                $queuedSysMsgCountList[$numInd] = SysMsgCountStringStorage::getInstance()->getValue($chatId);
            }

            $chatCountList = $this->model::getStorage()->exec();
            $sysMsgCountList = SysMsgCountStringStorage::getStorage()->exec();
        } catch (Exception $ex) {
            $this->model::getStorage()->discard();
            SysMsgCountStringStorage::getStorage()->discard();
        }

        if (empty($chatCountList)) {
            return [];
        }

        $result = [];
        foreach ($queuedMsgCountList as $numInd => $transRes) {
            // получим общее кол-во сообщений
            if ($transRes !== RedisBase::TRANSACTION_QUEUED) {
                continue;
            }
            $chatCount = (int) $chatCountList[$numInd];
            // получим кол-во системных сообщений
            if (
                $queuedSysMsgCountList[$numInd] === RedisBase::TRANSACTION_QUEUED
                && !empty($sysMsgCountList[$numInd])
            ) {
                $chatCount -= (int) $sysMsgCountList[$numInd];
            }
            $result[$chatIds[$numInd]] = $chatCount;
        }

        return $result;
    }

    protected function executeKickByEmail(string $userEmail, int $chatId): bool
    {
        $userItem = UserModel::getInstance()->getItemByEmail($userEmail);
        if (empty($userItem)) {
            return false;
        }

        $whereParams = ['userId' => $userItem['id'], 'chatId' => $chatId];
        $userChatItem = UserChatModel::getInstance()->getItemBy($whereParams);
        if (empty($userChatItem)) {
            return false;
        }
        $userChatItem['isUserBanned'] = UserChatModel::IS_USER_BANNED_YES;

        return (int) UserChatModel::getInstance()->updateBy($userChatItem, $whereParams);
    }

    protected function executeSendMsgWithDelay(MessageAddForm $form, array &$cmdList): bool
    {
        if (empty($cmdList)) {
            return false;
        }
        array_shift($cmdList);
        $timeout = array_shift($cmdList);

        return (bool) DelayMsgSortedSetStorage::getInstance()
            ->addTo(
                time() + (int) $timeout, [
                'c' => $form->chatId,
                'u' => $form->userId,
                'm' => implode(' ', $cmdList)
            ]);
    }

    protected function executeClearHistoryCmd(MessageAddForm $form): bool
    {
        $this->model->delete($form->chatId);
        $form->message = 'История чата была успешно удалена в ' . date('Y-m-d H:i:s');
        // удалим значение "количество системных комманд"
        SysMsgCountStringStorage::getInstance()->delete($form->chatId);
        // сохраним сообщение
        return $this->insertMessage($form->userId, $form->chatId, $form->message, $form->messageType);
    }
}
