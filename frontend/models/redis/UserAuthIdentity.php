<?php
namespace frontend\models\redis;

use common\models\mysql\UserModel;
use Exception;
use yii\base\Model;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

class UserAuthIdentity extends Model implements IdentityInterface
{
    protected $_user = null;

    public function isUserExists(string $email): bool
    {
        $this->_user = UserModel::getInstance()->getItemByEmail($email);
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
        $user = UserModel::getInstance()->getItemById((int) $id);
        if (empty($user)) {
            return null;
        }

        return self::createFromParams($user);
    }

    public static function createFromParams(array $user): ?self
    {
        $identity = new self();
        $identity->_user = $user;
        if (!$identity->validate()) {
            return null;
        }

        return $identity;
    }

    public function createUserFromEmail(?string $email): bool
    {
        $user = [
            'email' => $email,
            'name' => '',
            'status' => UserModel::STATUS_ENABLED,
            'createdAt' => date('Y-m-d H:i:s'),
        ];
        $userId = UserModel::getInstance()->insertBy($user);
        if (empty($userId)) {
            return false;
        }
        $user['id'] = $userId;
        $this->_user = $user;

        return true;
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

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('Error when identity by tocken');
    }

    public function getAuthKey()
    {
        throw new Exception('UserAuthIdentity::getAuthKey');
    }

    public function validateAuthKey($authKey)
    {
        throw new Exception('UserAuthIdentity::ValidateAuthKey') ;
    }
}
