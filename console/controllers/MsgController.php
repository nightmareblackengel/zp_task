<?php
namespace console\controllers;

use common\ext\console\ConsoleController;
use common\models\mysql\ChatModel;

class MsgController extends ConsoleController
{
    const MAX_DAY_COUNT = 30;
    const MAX_MESSAGE_COUNT = 5000;

    // docker exec -it mphp /var/www/html/ztt.loc/yii msg/delete-by-settings

    // поддержка истории до [1 / 7 / 30 дней | 500 / 1000 / 5000 сообщений] в зависимости от настройки на пользователе / канале.
    // Внимание! Если 2 пользователя ведут диалог и у одного из них настройка 30 дней, а у второго 7 дней - история хранится по максимальному сроку / количеству сообщений.

    /**
     *  запуск раз в час
     * выполняет удаление сообщений согласно настройкам пользователя.
     * если у пользователя нет настроек (или у разных пользователей указаны дни и сообщения)
     * то сообщения будут удаляться по одному из максимальных условий: 5тыс сообщений или больше 30ти дней
     */
    public function actionDeleteBySettings()
    {
        $this->setMaxTimeAndMemory(60*60, '2048M');
        if (!$this->checkIfCorrectDockerRun()) {
            return '';
        }

        echo "Старт метода очистки сообщений. Время=", date('Y-m-d H:i:s'), PHP_EOL;

        // для каждого чата
        $chats = ChatModel::getInstance()->getList(['status' => ChatModel::STATUS_ENABLED], '`id`, `name`');
        if (empty($chats)) {
            echo 'Чаты отсуствуют!', PHP_EOL;
            return '';
        }

        foreach ($chats as $chat) {
            print_r2($chat);
            list($maxDay, $maxMessage) = $this->getChatUserSettingParams($chat['id']);
        }



        // получить настройки всех пользователей
        // если у них разные настройки,
        // то для каждой настройки получить максимальное значение и затем делать проверки

        echo "Метод успешно выполнен", PHP_EOL;
        return '';
    }

    protected function getChatUserSettingParams(int $chatId): array
    {
        var_dump($chatId); echo PHP_EOL;

        return [null, null];
    }
}
