<?php
namespace frontend\widgets;

use common\models\mysql\ChatModel;
use Yii;
use yii\base\Widget;

class LeftNavBar extends Widget
{
    public function run()
    {
        return $this->render('@frontend/views/widgets/left-nav-bar', [
            'chatList' => $this->getChatData(),
            'requestChatId' => (int) Yii::$app->request->get('chat_id'),
        ]);
    }

    protected function getChatData(): array
    {
        $userInfo = Yii::$app->controller->getCurrentUser();
        if (empty($userInfo)) {
            return [];
        }

        return ChatModel::getChatList($userInfo['id']);
    }
}
