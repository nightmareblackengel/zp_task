<?php
namespace frontend\controllers;

use common\ext\widgets\ActiveForm;
use common\models\ChatMessageModel;
use common\models\mysql\ChatModel;
use common\models\mysql\UserChatModel;
use common\models\mysql\UserModel;
use frontend\ext\AuthController;
use frontend\ext\helpers\Url;
use frontend\models\forms\ChatAddUserForm;
use frontend\models\forms\ChatCreateForm;
use frontend\models\forms\MessageAddForm;
use frontend\models\forms\UserSettingsForm;
use frontend\models\helpers\AjaxHelper;
use frontend\widgets\CookieAlert;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

class ChatController extends AuthController
{
    public $allowedUnAuthActions = ['ajax-load'];

    public function actionIndex()
    {
        $this->layout = '_chat_index';

        return $this->render('index');
    }

    public function actionAjaxLoad()
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax || !Yii::$app->request->isPost) {
            return $this->ajaxErr('Ошибка! Некорректный тип переданных данных');
        }
        if (!$this->hasAccess()) {
            return $this->ajaxErr('Время авторизации истекло. Обновите пожалуйста страницу, для повторной авторизации.');
        }

        $form = new AjaxHelper();
        if (!$form->load(Yii::$app->request->post())) {
            return $this->ajaxErr($form->getDefaultError());
        }

        $userChatItem = [];
        if (!empty($form->chat->id)) {
            $userChatItem = $this->getUserChatItem($form->userId, $form->chat->id);
            if (empty($userChatItem)) {
                return $this->ajaxErr('Ошибка 403! У Вас нет доступа к этому чату');
            }
        }

        return $form->prepareData($userChatItem);
    }

    public function actionCreateMsg()
    {
        $this->layout = false;
        Yii::$app->response->format = Response::FORMAT_JSON;
        $formModel = new MessageAddForm();

        if (!$formModel->load(Yii::$app->request->post())) {
            return [
                'result' => AjaxHelper::AJAX_RESPONSE_ERR,
                'message' => 'Некорректные параметры запроса!',
            ];
        }

        $userChatItem = $this->getUserChatItem($formModel->userId, $formModel->chatId);
        if (empty($userChatItem)) {
            return [
                'result' => AjaxHelper::AJAX_RESPONSE_ERR,
                'message' => 'Ошибка 403! Доступ запрещен!',
            ];
        }
        if ($userChatItem['isUserBanned'] === UserChatModel::IS_USER_BANNED_YES) {
            return [
                'result' => AjaxHelper::AJAX_RESPONSE_ERR,
                'message' => 'Вы больше не можете отправлять сообщения в этом чате, т.к. владелец чата Вас забанил!)',
            ];
        }

        $formErrors = ActiveForm::validate($formModel);
        if (!empty($formErrors)) {
            return [
                'result' => AjaxHelper::AJAX_RESPONSE_ERR,
                'form_err' => $formErrors,
            ];
        }

        if (!ChatMessageModel::getInstance()->saveMessageFrom($formModel)) {
            return [
                'result' => AjaxHelper::AJAX_RESPONSE_ERR,
                'message' => $formModel->getDefaultError(),
            ];
        }

        return [
            'result' => AjaxHelper::AJAX_RESPONSE_OK,
        ];
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
            'results' =>  UserModel::getInstance()->getExceptList($exceptList, $searchText),
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
        // TODO: переделать функционал, чтобы не держать 1млн пользователей
        $usersForm->userCanAddIds = UserModel::getInstance()->getUserListForAddToChannel($chatId);

        if ($usersForm->load(Yii::$app->request->post())) {
            $usersForm->chatId = $chatId;

            if (!empty($usersForm->userCanAddIds)) {
                $saveRes = $usersForm->save();
                if ($saveRes) {
                    return $this->redirect(
                        Url::to(['/chat/index', 'chat_id' => $chatId])
                    );
                }
            }
        }

        return $this->render('add-user-to-channel', [
            'usersForm' => $usersForm,
            'chat' => $chat,
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
            'result' => AjaxHelper::AJAX_RESPONSE_ERR,
            'message' => $message,
        ];
    }

    protected function getUserChatItem(?int $userId, ?int $chatId): array
    {
        return UserChatModel::getInstance()->getItemBy([
            'userId' => $userId,
            'chatId' => $chatId,
        ], '`userId`, `chatId`, `isUserBanned`, `isChatOwner`');
    }
}
