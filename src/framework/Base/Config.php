<?php

namespace Framework;

use Dotenv\Dotenv;
use Framework\Traits\Singleton;

class Config
{
    use Singleton;

    protected function init()
    {
        $this->loadEnv();
    }

    protected function loadEnv()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
        $dotenv->load();
    }


    public function getEnv(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}
