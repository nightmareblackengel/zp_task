<?php
namespace frontend\controllers;

use common\models\ChatMessageModel;
use frontend\ext\AuthController;
use frontend\ext\helpers\Url;
use frontend\models\forms\ChatAddUserForm;
use frontend\models\forms\ChatCreateForm;
use frontend\models\forms\ConnectToChannelForm;
use frontend\models\mysql\UserChatModel;
use frontend\models\mysql\ChatModel;
use frontend\models\mysql\UserModel;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ChatController extends AuthController
{
    public function actionIndex()
    {
        $this->layout = '_chat_index';

        return $this->render('index');
    }

    public function actionCreateChat()
    {
        $this->layout = '_chat_index';
        $formModel = new ChatCreateForm();

        if ($formModel->load(Yii::$app->request->post())) {
            $chatId = $formModel->save();
            if ($chatId) {
                return $this->redirect(
                    Url::to(['/chat/index', 'chat_id' => $chatId])
                );
            }
        }

        return $this->render('create-chat', [
            'formModel' => $formModel,
        ]);
    }

    public function actionUserList()
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $exceptParam = (int) Yii::$app->request->get('except_users');
        $chatId = (int) Yii::$app->request->get('chat_id');

        $searchText = Yii::$app->request->get('q');
        $userItem = $this->getCurrentUser();
        if (empty($userItem['id']) || empty($chatId) || empty($searchText) || mb_strlen($searchText) < 2) {
            return [];
        }
        $exceptList = [$userItem['id']];
        if ($exceptParam === 1) {
            $userIds = array_keys(UserModel::getInstance()->getUserListForChat($chatId));
            if (!empty($userIds)) {
                $exceptList = $userIds;
            }
        }

        return [
            'results' => UserModel::getInstance()->getExceptList($exceptList, $searchText),
        ];
    }

    public function actionAddUserToChannel()
    {
        $this->layout = '_chat_index';
        $userItem = $this->getCurrentUser();
        $chatId = Yii::$app->request->get('chat_id');
        if (empty($chatId)) {
            throw new ForbiddenHttpException('Некорректные параметры');
        }
        $isChatOwner = UserChatModel::getInstance()->isUserChatOwner($userItem['id'], $chatId);
        if (!$isChatOwner) {
            throw new ForbiddenHttpException('У Вас нет прав редактировать этот чат');
        }
        $chat = ChatModel::getInstance()->getItemBy(['id' => $chatId]);
        if ($chat['isChannel'] !== ChatModel::IS_CHANNEL_TRUE) {
            throw new ForbiddenHttpException('Вы не можете добавлять пользователей');
        }

        $usersForm = new ChatAddUserForm(['chatId' => $chatId]);
        $usersForm->existsUsers = UserModel::getInstance()->getUserListForChat($chatId);

        if ($usersForm->load(Yii::$app->request->post())) {
            $usersForm->chatId = $chatId;

            $saveRes = $usersForm->save();
            if ($saveRes) {
                return $this->redirect(
                    Url::to(['/chat/index', 'chat_id' => $chatId])
                );
            }
        }

        return $this->render('add-user-to-channel', [
            'usersForm' => $usersForm,
            'chat' => $chat,
        ]);
    }

    public function actionConnectToChannel()
    {
        $user = $this->getCurrentUser();
        $this->layout = '_chat_index';

        $formModel = new ConnectToChannelForm();
        if ($formModel->load(Yii::$app->request->post()) && $formModel->save()) {
            ChatMessageModel::getInstance()->insertMessage(
                $user['id'], $formModel->channelId, 'Добавлен пользователь [' . $user['concatName'] . '] ', ChatMessageModel::MESSAGE_TYPE_SYSTEM
            );

            return $this->redirect(['chat/index', 'chat_id' => $formModel->channelId]);
        }

        return $this->render('connect-to-channel', [
            'formModel' => $formModel,
            'userId' => $user['id'],
        ]);
    }

    public function actionChannelList()
    {
        $userId = (int) Yii::$app->request->get('user_id');
        $searchText = Yii::$app->request->get('q');
        if (empty($userId)) {
            throw new NotFoundHttpException();
        }
        $user = $this->getCurrentUser();
        if (empty($user['id']) || $user['id'] !== $userId) {
            throw new ForbiddenHttpException();
        }
        if (empty($searchText) || mb_strlen($searchText) < 2) {
            return [];
        }
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;

        return [
            'results' => ChatModel::getInstance()->getChannelList($userId, $searchText),
        ];
    }
}
