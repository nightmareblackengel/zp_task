<?php

namespace frontend\models\forms;

use common\ext\base\Form;
use common\models\ChatMessageModel;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use common\models\mysql\UserModel;
use frontend\models\helpers\MessageCommandHelper;
use Yii;

class MessageAddForm extends Form
{
    const SEND_MSG_WITH_DELAY_MIN_THR = 5;
    const SEND_MSG_WITH_DELAY_MAX_THR = 3600;

    public ?string $message = null;
    public ?int $userId = null;
    public ?int $chatId = null;
    public ?int $messageType = null;

    public function rules()
    {
        return [
            [['message', 'chatId', 'userId', 'messageType'], 'required'],
            [['message'], 'string', 'max' => 1000],
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

        if (empty($this->userId) || empty($this->chatId)) {
            return false;
        }

        return $res;
    }

    public function allowedCommandsRule($attribute)
    {
        $value = $this->$attribute;
        if (empty($value) || !is_string($value)) {
            return;
        }
        if ($value[0] !== '/') {
            return;
        }

        $value = preg_replace('`[\\ ]+`', ' ', $value);
        $cmdList = explode(' ', $value);
        if (empty($cmdList)) {
            return;
        }

        $allowedCmds = array_keys(MessageCommandHelper::$chatDescriptions);
        if (!in_array($cmdList[0], $allowedCmds)) {
            return $this->addError($attribute, 'Некорректная комманда!');
        }

        $chat = ChatModel::getInstance()->getItemBy(['id' => $this->chatId]);
        $isChannel = $chat['isChannel'] ?? 0;

        if (in_array($cmdList[0], MessageCommandHelper::$channelList) && empty($isChannel)) {
            return $this->addError($attribute, 'Данную комманду можно использовать только в канале.');
        }

        // дополнительные правила
        if ($cmdList[0] === MessageCommandHelper::MSG_CHAT_CMD_BAN) {
            if (count($cmdList) !== 2 || empty($cmdList[1])) {
                return $this->addError($attribute, 'Некорректный формат комманды. Пример комманды: "/ban alex@gmail.com"');
            }
            $ownerUserChatRecord = UserChatModel::getInstance()->getItemBy([
                'userId' => $this->userId,
                'chatId' => $this->chatId,
            ]);
            if (UserChatModel::IS_CHAT_OWNER_NO === $ownerUserChatRecord['isChatOwner']) {
                return $this->addError($attribute, 'Данную комманду может выполнять только владелец канала.');
            }

            // по емейлу - получим пользователя "над которым" совершаем комманду
            $cmdToUser = UserModel::getInstance()->getItemByEmail($cmdList[1]);
            if (empty($cmdToUser)) {
                return $this->addError($attribute, 'Пользователя, с указанным емейлом - не существует!');
            }
            $cmdUserChatRecord = UserChatModel::getInstance()->getItemBy([
                'userId' => $cmdToUser['id'],
                'chatId' => $this->chatId,
            ]);
            if (empty($cmdUserChatRecord)) {
                return $this->addError($attribute, 'В этом чате нет пользователя, с указанным емейлом');
            }
            if (UserChatModel::IS_USER_BANNED_YES === $cmdUserChatRecord['isUserBanned']) {
                return $this->addError($attribute, 'Данный пользователь уже был забанен!');
            }
            if (UserChatModel::IS_CHAT_OWNER_YES === $cmdUserChatRecord['isChatOwner']) {
                return $this->addError($attribute, 'Вы не можете забанить владельца канала!)');
            }
        } elseif ($cmdList[0] === MessageCommandHelper::MSG_CMD_ME) {
            if (count($cmdList) < 2 || empty($cmdList[1])) {
                return $this->addError($attribute, 'Некорректный формат комманды. Пример комманды: "/me текст сообщения"');
            }
        } elseif ($cmdList[0] === MessageCommandHelper::MSG_CMD_SEND_MSG_WITH_DELAY) {
            if (count($cmdList) < 3 || empty($cmdList[2])) {
                return $this->addError($attribute, 'Некорректный формат комманды. Пример комманды: "/sendwithdelay 5 сообщение с пробелами"');
            }
            $cmdList[1] = (int) $cmdList[1];
            if ($cmdList[1] < self::SEND_MSG_WITH_DELAY_MIN_THR || $cmdList[1] > self::SEND_MSG_WITH_DELAY_MAX_THR) {
                $this->addError(
                    $attribute,
                    sprintf(
                        'Некорректный формат комманды sendwithdelay. Количество секунд указано неверно. Минимальное значение =%d. Максмальное значение=%d.',
                        self::SEND_MSG_WITH_DELAY_MIN_THR,
                        self::SEND_MSG_WITH_DELAY_MAX_THR
                    )
                );
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
