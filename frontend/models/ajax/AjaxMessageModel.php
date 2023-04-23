<?php

namespace frontend\models\ajax;

use common\models\ChatMessageModel;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use common\models\mysql\UserModel;
use common\models\redis\ChatMessageQueueStorage;
use frontend\models\helpers\AjaxHelper;
use Yii;

class AjaxMessageModel extends AjaxBase
{
    const MAX_MSG_GET_AT_ONCE = 50;

    public ?int $lastUpdatedAt = null;
    public ?int $prevMsgCount = null;
    public ?int $showedMsgCount = null;

    public function load(?array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $this->lastUpdatedAt = (int) ($data['last_updated_at'] ?? 0);
        $this->showInResponse = (int) ($data['show_in_response'] ?? 0);
        $this->prevMsgCount = (int) ($data['chat_msg_count'] ?? 0);
        $this->showedMsgCount = (int) ($data['showed_msg_count'] ?? 0);

        return true;
    }

    public function prepareResponse(?int $userId, ?int $chatId, ?array $params = []): ?array
    {
        if (empty($this->showInResponse) || $this->showInResponse === AjaxHelper::AJAX_REQUEST_EXCLUDE) {
            return null;
        }
        if (empty($chatId)) {
            return [
                'result' => AjaxHelper::AJAX_RESPONSE_OK,
                'messages_count' => false,
                'html' => Yii::$app->controller->render('/ajax/messages_empty'),
            ];
        }

        $chat = ChatModel::getInstance()->getItemBy(['id' => $chatId]);
        $chatOwnerId = UserChatModel::getInstance()->getChatOwnerId($chatId);
        $messagesCount = (int) ChatMessageQueueStorage::getInstance()->getQueueLength($chatId);
        $responsePlace = AjaxHelper::AJAX_RESPONSE_PLACE_APPEND;
        $offset = 0;
        $messages = [];
        $previousMsgEnd = 0;
        $showLoader = false;

        if ($this->showInResponse === AjaxHelper::AJAX_REQUEST_INCLUDE) {
            $responsePlace = AjaxHelper::AJAX_RESPONSE_PLACE_NEW;

            if ($messagesCount > self::MAX_MSG_GET_AT_ONCE) {
                $showLoader = true;
                if (($messagesCount - self::MAX_MSG_GET_AT_ONCE) >= 0) {
                    $offset = $messagesCount - self::MAX_MSG_GET_AT_ONCE;
                }
            }
            $messages = ChatMessageModel::getInstance()
                ->getList($chatId, $offset, - 1);
        } elseif ($this->showInResponse === AjaxHelper::AJAX_REQUEST_CHECK_NEW) {
            $responsePlace = AjaxHelper::AJAX_RESPONSE_PLACE_APPEND;
            // если у пользователя сейчас отображается фраза "Вы не написали еще ни одного сообщения!"
            if ($messagesCount === 0) {
                $messages = null;
            } elseif ($this->prevMsgCount <> $messagesCount) {
                $messages = ChatMessageModel::getInstance()
                    ->getList($chatId, $this->prevMsgCount, $messagesCount - 1);
                // для того, чтобы заменить стартовую надпись, при первом сообщении
                if (0 === $this->prevMsgCount) {
                    $responsePlace = AjaxHelper::AJAX_RESPONSE_PLACE_NEW;
                }
            }
        } elseif ($this->showInResponse === AjaxHelper::AJAX_REQUEST_CHECK_PREV) {
            $responsePlace = AjaxHelper::AJAX_RESPONSE_PLACE_PREPEND;
            if (!empty($this->showedMsgCount)) {
                $endPos = $messagesCount - $this->showedMsgCount;
                $startPos = $endPos - self::MAX_MSG_GET_AT_ONCE;
                if ($startPos <= 0) {
                    $previousMsgEnd = 1;
                    $startPos = 0;
                } else {
                    $showLoader = true;
                }

                $messages = ChatMessageModel::getInstance()
                    ->getList($chatId, $startPos, $endPos - 1);
            }
        }
        $html = null;
        if (null !== $messages) {
            $html = Yii::$app->controller->render('/ajax/messages', [
                'userList' => UserModel::getInstance()->getUserListForChat($chatId, true),
                'messages' => $messages,
                'currentUserId' => $userId,
                'chat' => $chat,
                'chatOwnerId' => $chatOwnerId,
                'messageCount' => $messagesCount,
                'responsePlace' => $responsePlace,
                'showLoader' => $showLoader,
            ]);
        }

        return [
            'result' => AjaxHelper::AJAX_RESPONSE_OK,
            'msgAddType' => $responsePlace,
            'messages_count' => $messagesCount,
            'previous_msg_is_end' => $previousMsgEnd,
            'html' => $html,
        ];
    }
}
