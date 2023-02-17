<?php

namespace frontend\models\forms;

use common\ext\base\Form;
use common\models\ChatMessageModel;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use frontend\models\helpers\MessageCommandHelper;
use Yii;

class MessageAddForm extends Form
{
    public ?string $message = null;
    public ?int $userId = null;
    public ?int $chatId = null;
    public ?int $messageType = null;

    public function rules()
    {
        return [
            [['message', 'chatId', 'userId', 'messageType'], 'required'],
            [['message'], 'string'],
            [['message'], 'allowedCommandsRule'],
            [['chatId', 'userId', 'messageType'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'message' => 'Сообщение',
        ];
    }

    public function load($data, $formName = null)
    {
        $res = parent::load($data, $formName);

        if (empty($this->messageType)) {
            $this->messageType = ChatMessageModel::MESSAGE_TYPE_SIMPLE;
        }
        $user = Yii::$app->controller->getCurrentUser();
        $this->userId = $user['id'];

        return $res;
    }

    public function allowedCommandsRule($attribute)
    {
        $value = $this->$attribute;
        if (empty($value) || !is_string($value)) {
            return false;
        }
        if ($value[0] !== '/') {
            return false;
        }

        $value = preg_replace('`[\\ ]+`', ' ', $value);
        $cmdList = explode(' ', $value);
        if (empty($cmdList)) {
            return false;
        }

        $allowedCmds = array_keys(MessageCommandHelper::$chatDescriptions);
        if (!in_array($cmdList[0], $allowedCmds)) {
            $this->addError($attribute, 'Некорректная комманда!');
            return true;
        }

        $chat = ChatModel::getInstance()->getItemBy(['id' => $this->chatId]);
        $isChannel = $chat['isChannel'] ?? 0;

        if (in_array($cmdList[0], MessageCommandHelper::$channelList) && empty($isChannel)) {
            $this->addError($attribute, 'Данную комманду можно использовать только в канале.');
            return true;
        }

        // дополнительные правила
        if ($cmdList[0] === MessageCommandHelper::MSG_CHAT_CMD_KICK) {
            if (count($cmdList) !== 2 || empty($cmdList[1])) {
                $this->addError($attribute, 'Некорректный формат комманды. Пример комманды: "/kick alex@gmail.com"');
                return true;
            }
            $userExists = UserChatModel::getInstance()->isUserEmailBelongToChat($this->chatId, $cmdList[1]);
            if ($userExists === null) {
                $this->addError($attribute, 'Данный пользователь уже был забанен!');
            } elseif (!$userExists) {
                $this->addError($attribute, 'В этом чате нет пользователя, с указанным емейлом');
            }

        } elseif ($cmdList[0] === MessageCommandHelper::MSG_CMD_ME) {
            if (count($cmdList) < 2 || empty($cmdList[1])) {
                $this->addError($attribute, 'Некорректный формат комманды. Пример комманды: "/me текст сообщения"');
                return true;
            }
        } elseif ($cmdList[0] === MessageCommandHelper::MSG_CMD_SEND_MSG_WITH_DELAY) {
            if (count($cmdList) < 3 || empty($cmdList[2])) {
                $this->addError($attribute, 'Некорректный формат комманды. Пример комманды: "/sendwithdelay 5 сообщение с пробелами"');
                return true;
            }
            $cmdList[1] = (int) $cmdList[1];
            if ($cmdList[1] < 1) {
                $this->addError($attribute, 'Некорректный формат комманды sendwithdelay. Количество секунд указано неверно.');
            }
        } elseif ($cmdList[0] === MessageCommandHelper::MSG_CHAT_CMD_CLEAR_HISTORY) {
            $isChatOwner = UserChatModel::getInstance()->isUserChatOwner($this->userId, $this->chatId);
            if (!$isChatOwner) {
                $this->addError($attribute, 'Данную комманду может выполнять только владелец канала.');
            }
        }

        return false;
    }
}
