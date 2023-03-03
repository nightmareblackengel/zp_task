<?php

namespace common\ext\traits;

use ReflectionMethod;
use yii\base\InvalidArgumentException;

/**
 * Example
 * $item1 = ChatModel::getInstance()->cache('getItemBy', [['id' => $chatId]]);
 */
trait LocalCacheTrait
{
    protected array $_localCache = [];

    public function cache(string $methodName, array $arguments = [])
    {
        $className = get_class($this);
        if (!method_exists($this, $methodName)) {
            throw new InvalidArgumentException(sprintf('Method %s not exists', $methodName));
        }

        $key = json_encode($arguments);
        if (isset($this->_localCache[$className][$key])) {
            return $this->_localCache[$className][$key];
        }

        $reflectionMethod = new ReflectionMethod($className, $methodName);
        $result = $reflectionMethod->invoke($this, ... $arguments);

        if (empty($this->_localCache[$className])) {
            $this->_localCache[$className] = [];
        }

        $this->_localCache[$className][$key] = $result;

        return $result;
    }
}
