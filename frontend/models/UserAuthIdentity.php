<?php
namespace frontend\models;

use common\models\UserModel;
use frontend\ext\helpers\EncryptHelper;
use yii\base\Model;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

class UserAuthIdentity extends Model implements IdentityInterface
{
    protected $_user = null;

    public function findUserByEmail(string $email): bool
    {
        $this->_user = UserModel::getInstance()->getItemBy($email);
        if (empty($this->_user)) {
            return false;
        }

        return true;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ((int) $this->_user['status'] !== UserModel::STATUS_ENABLED) {
            return false;
        }

        return true;
    }

    public function getId(): ?int
    {
        return $this->_user['id'] ?? null;
    }

    public static function findIdentity($id)
    {
        $user = UserModel::getInstance()->getFullItem((int) $id);
        if (empty($user)) {
            return null;
        }
        $identity = new self();
        $identity->_user = $user;
        if (!$identity->validate()) {
            return null;
        }

        return $identity;
    }

    public function getUser()
    {
        return $this->_user;
    }

    public function getUserTitle()
    {
        if (!empty($this->_user['name'])) {
            return $this->_user['name'];
        }

        return $this->_user['email'];
    }
    /******************************************************/
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('Error when identity by tocken');
    }

    public function getAuthKey()
    {
        if (empty($this->_user)) {
            return null;
        }

        return EncryptHelper::encode($this->_user['id'], $this->_user['created_at']);
    }

    public function validateAuthKey($authKey)
    {
        echo "validateAuthKey";
        exit();
        if (empty($authKey) || empty($this->_user['email'])) {
            return false;
        }

        return $authKey === $this->_user['email'];
    }
}
