<?php
namespace frontend\controllers;

use common\models\ChatMessageModel;
use common\models\mysql\ChatModel;
use common\models\mysql\UserModel;
use frontend\ext\AuthController;
use frontend\ext\helpers\Url;
use frontend\models\forms\ChatCreateForm;
use frontend\models\forms\ChatMessageForm;
use frontend\models\forms\UserSettingsForm;
use frontend\widgets\CookieAlert;
use Yii;
use yii\web\Response;

class ChatController extends AuthController
{
    public function actionIndex()
    {
        $this->layout = '_chat_index';
        $formModel = new ChatMessageForm();

        if ($formModel->load(Yii::$app->request->post()) && $formModel->validate()) {
            if (ChatMessageModel::getInstance()->saveMessageFrom($formModel)) {
                return $this->redirect(Url::to(['/chat/index', 'chat_id' => $formModel->chatId]));
            }
            $formModel->addError('message', 'Unknown error!');
        }

        $messages = [];
        if (!empty($formModel->chatId)) {
            $messages = ChatMessageModel::getInstance()->getList($formModel->chatId, 0, 2000);
        }

        return $this->render('index', [
            'formModel' => $formModel,
            'messages' => $messages,
            'currentUserId' => Yii::$app->user->identity->getId(),
        ]);
    }

    // TODO: access check
    // TODO: csrf check
    public function actionAjaxLoad()
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;

        $userInfo = $this->getCurrentUser();
        if (empty($userInfo)) {
            return [];
        }

        // download chat list info
        // params:
        // $requestChatId

        // download message list

        $lastDownloadData = date('Y-m-d H:i:s');

        return [
            'result' => 1,
            'chats' => [
                'result' => 1,
                'html' => $this->render('@frontend/views/chat/ajax/chats', [
                    'chatList' => ChatModel::getChatList($userInfo['id']),
                    'requestChatId' => (int) Yii::$app->request->get('chat_id'),
                ]),
                'downloadedAt' => $lastDownloadData,
            ],
            'messages' => [
                'result' => 1,
                'chat_id' => 1,
                'html' => '',
                'downloadedAt' => $lastDownloadData,
            ]
        ];
    }

    public function actionCreate()
    {
        $this->layout = '_chat_index';
        $userItem = $this->getCurrentUser();

        $formModel = new ChatCreateForm();
        $userList = UserModel::getInstance()->getShortListExcept($userItem['id']);

        if ($formModel->load(Yii::$app->request->post())) {
            if ($formModel->save()) {
                CookieAlert::addMessage('Настройки были успешно сохранены');
                return $this->redirect(Url::to(['/chat/index', 'chat_id' => $formModel->id]));
            }
        }

        return $this->render('create', [
            'formModel' => $formModel,
            'userList' => $userList,
        ]);
    }

    public function actionSettings()
    {
        $this->layout = '_chat_default';
        $formModel = new UserSettingsForm();
        $formModel->userId = Yii::$app->user->identity->getId();
        $formModel->loadFromDb();

        if ($formModel->load(Yii::$app->request->post()) && $formModel->save()) {
            CookieAlert::addMessage('Настройки были успешно сохранены');
            return $this->redirect(Url::to('/chat/settings'));
        }

        return $this->render('settings', [
            'formModel' => $formModel,
        ]);
    }
}
