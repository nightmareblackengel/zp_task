<?php

namespace console\controllers;

use common\ext\console\ConsoleController;
use common\models\ChatMessageModel;
use common\models\mysql\ChatModel;
use common\models\mysql\UserModel;
use common\models\mysql\UserSettingsModel;
use console\models\helpers\MessageHelper;
use DateTime;
use Faker\Factory;

class TestController extends ConsoleController
{
    const MSG_CREATE_COUNT = 1; //~4sec for 10000
    const CHANNEL_USER_COUNT = 2; // ~3sec for 1000
    const CHANNEL_COUNT = 1; // ~2sec for 1000

    private $time;
    /**
     * docker exec -it mphp /var/www/html/ztt.loc/yii test/create-users
     *
     * Контроллер создает тестовые записи:
     * создает пользователей с настройками
     * создает чаты и подключает пользователей к чатам
     */
    public function actionCreateUsers($chatCount = null)
    {
        $chatCount = (int) $chatCount;
        if (empty($chatCount)) {
            $chatCount = self::CHANNEL_COUNT;
        }
        if ($chatCount < 0 || $chatCount > 1000) {
            $chatCount = 1;
        }

        echo "Будет создано " . $chatCount . " чатов", PHP_EOL;
        $faker = Factory::create('ru_RU');
        $this->time = DateTime::createFromFormat('U.u', microtime(true));

        for ($c = 0; $c < $chatCount; $c++) {
            $usersCount = self::CHANNEL_USER_COUNT;
            echo "Будет создано " . $usersCount . " пользователей", PHP_EOL;
            $userIds = [];
            for ($i = 0; $i < $usersCount; $i++) {
                // users
                $userId = $this->createNewUser();
                if (!empty($userId)) {
                    $userIds[] = $userId;
                    $this->createUserSetting($userId);
                }
            }
            $this->print_difference('users created');
            echo "Было создано " . count($userIds) . " пользователей для текущего чата", PHP_EOL;

            if (!empty($userIds)) {
                $owner = array_shift($userIds);
                $newChatId = ChatModel::getInstance()
                    ->saveChat(
                        'НазвЧата' . microtime(true),
                        true,
                        $userIds,
                        $owner,
                    );
                $this->print_difference('chat created');
                if (!empty($newChatId)) {
                    $insertedMsgCount = MessageHelper::generateNewMessages($faker, [$owner] + $userIds, $newChatId, rand(self::MSG_CREATE_COUNT, self::MSG_CREATE_COUNT + 1));

                    echo "Чат id=" . $newChatId . ' был создан; В этот чат добавленое ', $insertedMsgCount, ' сообщений', PHP_EOL;
                    $this->print_difference('msg created');
                }
            }
        }
        echo "Создание тестовых данных - завершено", PHP_EOL;
        $this->print_difference();
        return '';
    }

    protected function createUserSetting(int $userId): bool
    {
        $settType = rand(1, 2);
        $settValue = rand(1, 3);
        if ($settType === UserSettingsModel::HISTORY_STORE_TYPE_MESSAGE) {
            $settValue += 10;
        }

        return (bool) UserSettingsModel::getInstance()->insertBy([
            'userId' => $userId,
            'historyStoreType' => $settType,
            'historyStoreTime' => $settValue,
        ]);
    }

    protected function createNewUser(): int
    {
        return (int) UserModel::getInstance()->insertBy([
            'email' => 'email' . microtime(true) . '@tst.ua',
            'status' => UserModel::STATUS_ENABLED,
        ]);
    }

    public function print_difference($message = '')
    {
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $dateInterval = $now->diff($this->time);
        echo PHP_EOL, $message, $dateInterval->format('[%H:%I:%S.%F]'), PHP_EOL;
        $this->time = $now;
    }

    // docker exec -it mphp /var/www/html/ztt.loc/yii test/add-message-cont
    // автоматически отправляет сообщение в указанный чат от указанного пользователя с указанным интервалом
    public function actionAddMessageCont($channelId = 11424, $userId = 2, $count = 2000, $sleep = 1)
    {
        $count = (int) $count;
        set_time_limit($count);
        $i = 2;
        while ($i < $count) {
            sleep((int) $sleep);
            ChatMessageModel::getInstance()
                ->insertMessage(
                    (int) $userId,
                    (int) $channelId,
                    $i,
                    ChatMessageModel::MESSAGE_TYPE_SIMPLE,
                    time()
                );
            echo $i, PHP_EOL;
            $i++;
        }
    }
}
