<?php
namespace frontend\models\forms;

use common\ext\base\Form;
use common\models\UserModel;
use frontend\models\UserAuthIdentity;
use Yii;

class AuthForm extends Form
{
    const AUTH_DURATION = 7*24*60*60;

    public string $email = '';

    public function rules(): array
    {
        return [
            [['email'], 'email'],
            [['email'], 'string', 'max' => 255],
        ];
    }

    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $identity = new UserAuthIdentity();
        if (!$identity->findUserByEmail($this->email)) {
            $this->addError('email', 'Error! User not exists!');
            return false;
        }
        if (!$identity->validate()) {
            $this->addError('email', 'Error! User deactivated!');
            return false;
        }

        return Yii::$app->user->login($identity, self::AUTH_DURATION);
    }
}
