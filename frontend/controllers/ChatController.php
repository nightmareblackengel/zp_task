<?php
namespace frontend\controllers;

use common\models\ChatMessageModel;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
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
    const AJAX_RESULT_OK = 1;
    const AJAX_RESULT_NOT_FILLED = 2;
    const AJAX_RESULT_ERR = 3;

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

        return $this->render('index', [
            'formModel' => $formModel,
        ]);
    }

    // TODO: access check
    public function actionAjaxLoad()
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (empty($this->userArr)) {
            return $this->ajaxErr('Ошибка! данный пользователь не найден.');
        }
        if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
            return $this->ajaxErr('Ошибка! Некорректный тип переданных данных');
        }
        $chatId = (int) Yii::$app->request->post('requestChatId');
        $messages = false;
        if (!emptY($chatId)) {
            $hasAccess = UserChatModel::getInstance()->isUserBelongToChat($this->userArr['id'], $chatId);
            if (!$hasAccess) {
                return $this->ajaxErr('Ошибка 403! У Вас нет доступа к этому чату');
            }
            // download message list
            $messages = ChatMessageModel::getInstance()
                ->getList($chatId, 0, 2000);
        }

        return [
            'result' => self::AJAX_RESULT_OK,
            'chats' => [
                'result' => 1,
                'html' => $this->render('/chat/ajax/chats', [
                    'chatList' => ChatModel::getChatList($this->userArr['id']),
                    'requestChatId' => $chatId,
                ]),
                'downloadedAt' => time(),
            ],
            'messages' => [
                'result' => self::AJAX_RESULT_OK,
                'chat_id' => $chatId ?? 0,
                'html' => $this->render('/chat/ajax/messages', [
                    'messages' => $messages,
                    'currentUserId' => $this->userArr['id'],
                ]),
                'downloadedAt' => time(),
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

    protected function ajaxErr($message)
    {
        return [
            'result' => self::AJAX_RESULT_ERR,
            'message' => $message,
        ];
    }
}
