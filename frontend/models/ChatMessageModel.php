<?php
namespace frontend\models;

use common\ext\helpers\Html;
use common\ext\redis\RedisBase;
use common\models\mysql\UserChatModel;
use common\models\redis\DelayMsgSortedSetStorage;
use Exception;
use frontend\models\forms\MessageAddForm;
use frontend\models\helpers\MessageCommandHelper;
use frontend\models\mysql\UserModel;
use frontend\models\redis\FlashMsgSetStorage;

class ChatMessageModel extends \common\models\ChatMessageModel
{
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
            } elseif ($cmdList[0] === MessageCommandHelper::MSG_CHAT_CMD_BAN && count($cmdList)) {
                $this->executeBanByEmail($cmdList[1], $form->chatId);
                return true;
            } elseif ($cmdList[0] === MessageCommandHelper::MSG_CMD_SEND_MSG_WITH_DELAY) {
                $this->executeSendMsgWithDelay($form, $cmdList);
                return true;
            } elseif ($cmdList[0] === MessageCommandHelper::MSG_CHAT_CMD_SHOW_MEMBERS) {
                $this->executeShowMembers($form);
                return true;
            }
        }

        return $this->insertMessage($form->userId, $form->chatId, $form->message, $form->messageType);
    }

    public function getChatListMsgCount(array &$chatIds): array
    {
        if (empty($chatIds)) {
            return [];
        }
        // prepare transaction for each 'chatId'
        $queuedMsgCountList = [];
        try {
            $this->model::getStorage()->multi();

            foreach ($chatIds as $numInd => $chatId) {
                $queuedMsgCountList[$numInd] = $this->model::getInstance()->getQueueLength($chatId);
            }

            $chatCountList = $this->model::getStorage()->exec();
        } catch (Exception $ex) {
            $this->model::getStorage()->discard();
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
            $result[$chatIds[$numInd]] = (int) $chatCountList[$numInd];
        }

        return $result;
    }

    protected function executeBanByEmail(string $userEmail, int $chatId): bool
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
        $this->insertMessage(
            $userItem['id'],
            $chatId,
            'Пользователь [' . $userEmail . '] забанен.',
            self::MESSAGE_TYPE_SYSTEM
        );

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
        $existsMsgCount = $this->model->getQueueLength($form->chatId);
        $this->model->delete($form->chatId);
        $form->message = 'История чата была успешно удалена в ' . date('Y-m-d H:i:s');
        FlashMsgSetStorage::getInstance()->setExValue($form->userId, 'Было удалено ' . $existsMsgCount . ' сообщений.');

        return $this->insertMessage($form->userId, $form->chatId, $form->message, $form->messageType);
    }

    protected function executeShowMembers(MessageAddForm $form): bool
    {
        $userList = UserModel::getInstance()->getUserListForChat($form->chatId, true);
        $resUsers = [];
        foreach ($userList as $userId => $userItem) {
            $userStr = Html::tag('span', Html::encode($userItem['name']), ['class' => 'userItemInCmdList']);
            if (UserChatModel::IS_USER_BANNED_YES === $userItem['isUserBanned']) {
                $userStr .= Html::tag('span', ' (забанен)', ['style' => 'color:red;']);
            }
            $resUsers[] = $userStr;
        }
        $msgStr = 'Список пользователей чата:<br/>' . implode('<br/>', $resUsers);

        return $this->insertMessage($form->userId, $form->chatId, $msgStr, static::MESSAGE_TYPE_SYSTEM);
    }
}
