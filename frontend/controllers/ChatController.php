<?php
namespace frontend\controllers;

use frontend\ext\AuthController;

class ChatController extends AuthController
{

    function actionTest1()
    {
        return $this->render('test1', [

        ]);
    }
}
