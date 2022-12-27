<?php
namespace frontend\controllers;

use frontend\ext\AuthController;
use frontend\models\forms\UserSettingsForm;
use Yii;

class ChatController extends AuthController
{
    public function actionIndex()
    {
        $this->layout = '_chat_index';

        return $this->render('index', [

        ]);
    }

    public function actionSettings()
    {
        $this->layout = '_chat_default';
        $formModel = new UserSettingsForm();
        $formModel->userId = Yii::$app->user->identity->getId();
        $formModel->loadFromDb();

        if ($formModel->load(Yii::$app->request->post()) && $formModel->save()) {
            # todo: show pretty message
            exit();
        }

        return $this->render('settings', [
            'formModel' => $formModel,
        ]);
    }
}
