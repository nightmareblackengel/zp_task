<?php
namespace frontend\controllers;

use common\models\mysql\UserModel;
use frontend\ext\AuthController;
use frontend\ext\helpers\Url;
use frontend\models\forms\ChatCreateForm;
use frontend\models\forms\UserSettingsForm;
use frontend\widgets\CookieAlert;
use Yii;

class ChatController extends AuthController
{
    public function actionIndex()
    {
        $this->layout = '_chat_index';

        return $this->render('index', [

        ]);
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
