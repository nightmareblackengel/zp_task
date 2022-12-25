<?php
namespace common\ext\patterns;

trait Singleton
{
    protected static $instance = [];

    /**
     * @return mixed | static
     */
    public static function getInstance()
    {
        $class = static::class;
        if (!isset(static::$instance[$class])) {
            static::$instance[$class] = new static();
        }

        return static::$instance[$class];
    }
}
