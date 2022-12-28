<?php
namespace frontend\models\forms;

use common\ext\base\Form;
use common\models\mysql\UserModel;
use frontend\models\helpers\AuthCookieHelper;
use frontend\models\UserAuthIdentity;
use Yii;

class AuthForm extends Form
{
    public string $email = '';

    public string $cap = '';

    public function rules(): array
    {
        return [
            [['email'], 'email'],
            [['email'], 'string', 'max' => 255],
//            [['cap'], 'required'],
            ['cap', 'captcha', 'captchaAction'=>'/main/captcha'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'email' => 'E-mail',
            'cap' => 'Каптча',
        ];
    }

    public function login(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $identity = new UserAuthIdentity();
        $userExists = $identity->isUserExists($this->email);
        if (!$userExists) {
            $createRes = $identity->createUserFromEmail($this->email);
            if (!$createRes) {
                $msgErr = 'Возникла ошибка при сохранении в БД.';
                if (count(UserModel::getInstance()->errors)) {
                    $msgErr = UserModel::getInstance()->errors[0];
                }
                $this->addError('email', $msgErr);
                return false;
            }
        }

        if (!$identity->validate()) {
            $this->addError('email', 'Error! User deactivated!');
            return false;
        }

        return Yii::$app->user->login($identity, AuthCookieHelper::AUTH_TIMEOUT);
    }
}
