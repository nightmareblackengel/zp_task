<?php
namespace frontend\controllers;

use common\models\ChatMessageModel;
use common\models\mysql\UserModel;
use frontend\ext\AuthController;
use frontend\ext\helpers\Url;
use frontend\models\forms\AjaxChatForm;
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

        $form = new AjaxChatForm(['userId' => $this->userArr['id']]);
        if (!$form->load(Yii::$app->request->post()) || !$form->hasAccess()) {
            return $this->ajaxErr($form->getDefaultError());
        }

        return $form->prepareData();
    }

    public function actionCreateChat()
    {
        $this->layout = '_chat_index';
        $userItem = $this->getCurrentUser();

        $formModel = new ChatCreateForm();
        $userList = UserModel::getInstance()->getShortListExcept($userItem['id']);

        if ($formModel->load(Yii::$app->request->post())) {
            $chatId = $formModel->save();
            if ($chatId) {
                return $this->redirect(
                    Url::to(['/chat/index', 'chat_id' => $chatId /*, '#' => 'divChatId' . $chatId*/])
                );
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
            'result' => AjaxChatForm::AJAX_RESULT_ERR,
            'message' => $message,
        ];
    }

//    public function actionTest()
//    {
//
//    }
}
