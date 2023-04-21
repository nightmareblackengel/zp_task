<?php

namespace frontend\models\ajax;

use common\models\mysql\ChatModel;
use frontend\models\helpers\AjaxHelper;
use Yii;

class AjaxChatModel extends AjaxBase
{
    const SHOW_RESULT_BY_TIMEOUT = 30;

    public ?int $id = null;
    public ?int $lastUpdatedAt = null;

    public function load(?array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $this->id = (int) ($data['id'] ?? 0);
        $this->lastUpdatedAt = (int) ($data['last_updated_at'] ?? 0);
        $this->showInResponse = (int) ($data['show_in_response'] ?? 0);

        return true;
    }

    public function prepareResponse(?int $userId, ?int $chatId, ?array $params = []): ?array
    {
        // must show result by timeout
        $timeDiff = time() - $this->lastUpdatedAt;
        if (
            ($timeDiff < self::SHOW_RESULT_BY_TIMEOUT)
            && (empty($this->showInResponse) || $this->showInResponse === AjaxHelper::AJAX_REQUEST_EXCLUDE)
        ) {
            return null;
        }

        return [
            'result' => AjaxHelper::AJAX_RESPONSE_OK,
            'html' => Yii::$app->controller->render('/ajax/chats', [
                'chatList' => ChatModel::prepareChatListWithCount($userId),
                'requestChatId' => $chatId,
            ]),
            'downloaded_at' => time(),
        ];
    }
}
