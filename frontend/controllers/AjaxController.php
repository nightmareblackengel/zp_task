<?php

namespace frontend\controllers;

use common\ext\widgets\ActiveForm;
use common\models\mysql\UserChatModel;
use frontend\ext\AuthController;
use frontend\models\ChatMessageModel;
use frontend\models\forms\MessageAddForm;
use frontend\models\helpers\AjaxHelper;
use Yii;
use yii\web\Response;

class AjaxController extends AuthController
{
    public $allowedUnAuthActions = ['load'];

    public function actionLoad()
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

    protected function getUserChatItem(?int $userId, ?int $chatId): ?array
    {
        return UserChatModel::getInstance()->getItemBy([
            'userId' => $userId,
            'chatId' => $chatId,
        ], '`userId`, `chatId`, `isUserBanned`, `isChatOwner`');
    }
}
