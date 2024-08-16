<?php

namespace Framework\Traits;

trait Singleton
{
    protected static $instanse = null;

    public static function getInstanse(): self
    {
        self::$instanse ??= new static();

        return self::$instanse;
    }

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
    }

    final public function __clone()
    {
    }

    final public function __wakeup()
    {
    }
}
