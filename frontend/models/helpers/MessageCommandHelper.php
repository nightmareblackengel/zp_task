<?php

namespace frontend\models\helpers;

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
}
