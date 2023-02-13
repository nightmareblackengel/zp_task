<?php

namespace frontend\models\helpers;

use common\ext\helpers\Html;
use stdClass;

class MessageCommandHelper
{
    const MSG_CMD_DATE = '/date';
    const MSG_CMD_ME = '/me';
    const MSG_CMD_SEND_MSG_WITH_DELAY = '/sendwithdelay';

    const MSG_CHAT_CMD_SHOW_MEMBERS = '/showmembers';
    const MSG_CHAT_CMD_KICK = '/kick';
    const MSG_CHAT_CMD_CLEAR_HISTORY = '/clearhistory';

    public static $chatDescriptions = [
        self::MSG_CMD_DATE => '/date',
        self::MSG_CMD_ME => '/me {текст сообщения}',
        self::MSG_CMD_SEND_MSG_WITH_DELAY => '/sendwithdelay {N} {текст сообщения}',
        self::MSG_CHAT_CMD_SHOW_MEMBERS => '/showmembers',
        self::MSG_CHAT_CMD_KICK => '/kick {email}',
        self::MSG_CHAT_CMD_CLEAR_HISTORY => '/clearhistory',
    ];

    public static function printCmd(? string $cmd, stdClass $msgItem, array $userList)
    {
        $cmdList = explode(' ', preg_replace('`[\\ ]+`', ' ', $cmd));
        if (empty($cmdList)) {
            return false;
        }

        if ($cmdList[0] === self::MSG_CMD_DATE) {
            return date('Y-m-d H:i:s', (int) $msgItem->d);
        } elseif ($cmdList[0] === self::MSG_CMD_ME) {
            array_shift($cmdList);
            return $userList[$msgItem->u] . ': ' . Html::encode(implode(' ', $cmdList));
        }

        return $cmd;
    }
}
