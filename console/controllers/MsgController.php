<?php
namespace console\controllers;

use common\ext\console\ConsoleController;
use common\models\mysql\ChatModel;
use common\models\redis\ChatMessageQueueStorage;
use console\models\helpers\UserSettingsHelper;
use console\models\UserSettingsModel;

class MsgController extends ConsoleController
{
    const MAX_DAY_COUNT = 30;
    const MAX_MESSAGE_COUNT = 5000;

    const RECIEVE_QUEUE_MSG_COUNT = 1000;

    // docker exec -it mphp /var/www/html/ztt.loc/yii msg/delete-by-settings

    // TODO;
    // запуск каждую минуту, но нельзя запустить, больше 1го экземпляра

    // поддержка истории до [1 / 7 / 30 дней | 500 / 1000 / 5000 сообщений] в зависимости от настройки на пользователе / канале.
    // Внимание! Если 2 пользователя ведут диалог и у одного из них настройка 30 дней, а у второго 7 дней - история хранится по максимальному сроку / количеству сообщений.

    /**
     *  запуск раз в час
     * выполняет удаление сообщений согласно настройкам пользователя.
     * если у пользователя нет настроек (или у разных пользователей указаны дни и сообщения)
     * то сообщения будут удаляться по условию: 5тыс сообщений
     */
    public function actionDeleteBySettings()
    {
        $this->setMaxTimeAndMemory(60*60, '2048M');
        if (!$this->checkIfCorrectDockerRun()) {
            return '';
        }

        echo "Старт метода очистки сообщений. Время=", date('Y-m-d H:i:s'), PHP_EOL;

        $offset = 0;
        $limit = 0;
        $ids = [3, 4, 25, 27, 29, 30];

        // для каждого чата
        $chats = ChatModel::getInstance()->getList([
            'status' => ChatModel::STATUS_ENABLED,
            // TODO: REMOVE IT
            'id' => $ids,
        ], '`id`, `name`', $offset, $limit);
        if (empty($chats)) {
            echo 'Чаты отсуствуют!', PHP_EOL;
            return '';
        }

        foreach ($chats as $chat) {
            print_r2($chat);
            list($maxMessages, $maxDays) = $this->getChatUserSettingParams($chat['id']);

            echo PHP_EOL;
            var_dump($maxMessages, $maxDays);

            if (!empty($maxDays)) {
                $removeCount = $this->removeMessagesByDayCount($chat['id'], $maxDays);
            } else {
                $removeCount = $this->removeMessagesByMsgCount($chat['id'], $maxMessages);
            }
            echo 'Для чата id=[', $chat['id'], '] было удалено ' . $removeCount . ' сообщений.', PHP_EOL;
        }
        echo "Метод успешно выполнен", PHP_EOL;
        return '';
    }

    protected function removeMessagesByDayCount(int $chatId, int $allowedDayCount): int
    {
        $msgCountForRemove = $this->getChatExpiredByDayMsgCount($chatId, $allowedDayCount);
        if (empty($msgCountForRemove)) {
            return 0;
        }

        $remRes = ChatMessageQueueStorage::getInstance()->removeItemCount($chatId, $msgCountForRemove);
        if (empty($remRes)) {
            return 0;
        }

        return $msgCountForRemove;
    }

    protected function removeMessagesByMsgCount(int $chatId, int $allowedMsgCount): int
    {
        if (empty($allowedMsgCount) || empty($chatId) || $allowedMsgCount < 1) {
            return 0;
        }

        $queueMsgCount = (int) ChatMessageQueueStorage::getInstance()->getQueueLength($chatId);
        if ($queueMsgCount <= $allowedMsgCount) {
            return 0;
        }
        $diffCount = $queueMsgCount - $allowedMsgCount;

        $result = ChatMessageQueueStorage::getInstance()->removeItemCount($chatId, $diffCount);
        if ($result) {
            return $diffCount;
        }

        return 0;
    }

    /**
     * @return array|int[messageCount, daysCount]
     */
    protected function getChatUserSettingParams(int $chatId): array
    {
        $settings = UserSettingsModel::getInstance()->getChatUserSettings($chatId);

        return UserSettingsHelper::prepareSettings($settings);
    }

    /**
     * Метод для указанного чата получает все собщения (по пачкам),
     * и сравнивает даты сообщений
     * все сообщения которые не подходят по условию - будут посчитаны,
     * и вовзращены как результат
     * @param int $chatId
     * @param int $allowedDayCount
     * @return int
     */
    protected function getChatExpiredByDayMsgCount(int $chatId, int $allowedDayCount): int
    {
        if (empty($allowedDayCount) || $allowedDayCount < 1) {
            return 0;
        }
        $lastTime = time() - $allowedDayCount * 24*60*60;
        $receiveMsgIndex = 0;
        $running = true;

        while ($running) {
            $messages = ChatMessageQueueStorage::getInstance()->getList($chatId, $receiveMsgIndex, $receiveMsgIndex + self::RECIEVE_QUEUE_MSG_COUNT - 1);
            if (empty($messages)) {
                $running = false;
                continue;
            }

            $runSubCycle = true;
            $i = 0;
            while ($i < count($messages) && $runSubCycle) {
                if (!empty($messages[$i]->d) && $messages[$i]->d > 1 && $lastTime <= $messages[$i]->d) {
                    $runSubCycle = false;
                    continue;
                }
                $i++;
            }
            $receiveMsgIndex += $i;
            if (!$runSubCycle) {
                $running = false;
            }
        }

        return $receiveMsgIndex;
    }
}
