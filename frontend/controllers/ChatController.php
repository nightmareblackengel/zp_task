<?php
namespace frontend\controllers;

use frontend\ext\AuthController;

class ChatController extends AuthController
{
    public function actionIndex()
    {
        $this->layout = 'chat';

        return $this->render('index', [

        ]);
    }
}
