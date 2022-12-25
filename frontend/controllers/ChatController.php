<?php
namespace frontend\controllers;

use frontend\ext\AuthController;

class ChatController extends AuthController
{

    public function actionTest1()
    {
        return $this->render('test1', [

        ]);
    }

    public function actionIndex()
    {
        return '';
    }
}
