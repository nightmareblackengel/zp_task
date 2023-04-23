<?php
namespace frontend\controllers;

use frontend\ext\AuthController;
use frontend\ext\helpers\Url;
use frontend\models\forms\UserSettingsForm;
use frontend\widgets\CookieAlert;
use Yii;

class SettingsController extends AuthController
{
    public function actionIndex()
    {
        $this->layout = '_chat_default';
        $formModel = new UserSettingsForm();
        $formModel->userId = $this->userArr['id'];
        $formModel->loadFromDb();

        if ($formModel->load(Yii::$app->request->post()) && $formModel->save()) {
            CookieAlert::addMessage('Настройки были успешно сохранены');

            return $this->redirect(Url::to('/settings/index'));
        }

        return $this->render('index', [
            'formModel' => $formModel,
        ]);
    }
}