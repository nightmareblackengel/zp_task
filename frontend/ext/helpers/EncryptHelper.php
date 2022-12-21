<?php
namespace frontend\ext\helpers;

use Yii;

class EncryptHelper
{
    public static function encode($key, string $salt, int $maxKeyLen = 64, int $maxSaltLen = 31): string
    {
        $preparedKey = str_pad($key, $maxKeyLen, ' ');
        $preparedSalt = str_pad($salt, $maxSaltLen, ' ');
        $encryptedId = Yii::$app->security->encryptByKey($preparedKey, $preparedSalt);

        return base64_encode($encryptedId);
    }

    public static function decode($encodedData, string $salt, $maxSaltLen = 31): ?string
    {
        if (empty($encodedData)) {
            return null;
        }

        $bDecodedData = base64_decode($encodedData);
        if (empty($bDecodedData)) {
            return null;
        }

        $preparedSalt = str_pad($salt, $maxSaltLen, ' ');
        $decrypted = Yii::$app->security->decryptByKey($bDecodedData, $preparedSalt);
        if (empty($decrypted)) {
            return null;
        }

        return $decrypted;
    }
}
