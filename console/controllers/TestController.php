<?php

namespace console\controllers;

use common\ext\console\ConsoleController;
use common\models\mysql\ChatModel;
use common\models\mysql\UserModel;
use common\models\mysql\UserSettingsModel;
use console\models\helpers\MessageHelper;
use Faker\Factory;

class TestController extends ConsoleController
{
    /** docker exec -it mphp /var/www/html/ztt.loc/yii test/create-users
     * Контроллер создает тестовые записи:
     * создает пользователей с настройками
     * создает чаты и подключает пользователей к чатам
     */
    public function actionCreateUsers($chatCount = null)
    {
        $chatCount = (int) $chatCount;
        if (empty($chatCount)) {
            $chatCount = rand(5, 20);
        }
        if ($chatCount < 0 || $chatCount > 1000) {
            $chatCount = 1;
        }

        echo "Будет создано " . $chatCount . " чатов", PHP_EOL;
        $faker = Factory::create('ru_RU');

        for ($c = 0; $c < $chatCount; $c++) {
            $usersCount = rand(5, 20);
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
            echo "Было создано " . count($userIds) . " пользователей для текущего чата", PHP_EOL;

            if (!empty($userIds)) {
                $newChatId = ChatModel::getInstance()->saveChat(
                    'НазвЧата' . microtime(true),
                    true,
                    $userIds,
                    $userIds[0],
                );
                if (!empty($newChatId)) {
                    $insertedMsgCount = MessageHelper::generateNewMessages($faker, $userIds, $newChatId, rand(1000, 10000));
                    echo "Чат id=" . $newChatId . ' был создан; В этот чат добавленое ', $insertedMsgCount, ' сообщений', PHP_EOL;
                }
            }
        }
        echo "Создание тестовых данных - завершено", PHP_EOL;

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
}
