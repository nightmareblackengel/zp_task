<?php
namespace common\ext\patterns;

trait Singleton
{
    private static $instance;

    public static function getInstance(): self
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
