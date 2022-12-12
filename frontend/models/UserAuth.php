<?php
namespace frontend\models;

class UserAuth extends \yii\web\User
{
    public $identityClass = UserAuthIdentity::class;

    public $enableAutoLogin = true;

    public $identityCookie = ['name' => '_identity-ztt', 'httpOnly' => true];
}
