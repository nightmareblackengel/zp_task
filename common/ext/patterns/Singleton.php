<?php
namespace common\ext\patterns;

trait Singleton
{
    protected static $instance = [];

    public static function getInstance()
    {
        $class = static::class;
        if (!isset(static::$instance[$class])) {
            static::$instance[$class] = new static();
        }

        return static::$instance[$class];
    }
}
