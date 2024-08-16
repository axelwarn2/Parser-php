<?php

namespace Framework;

use Framework\Traits\Singleton;
use PDO;

class CDatabase
{
    use Singleton;

    protected string $driver = "";
    protected string $host = "";
    protected string $dbName = "";
    protected string $password = "";
    protected string $user = "";

    public readonly \PDO $connection;

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {
        $this->loadConfiguration();
        $this->makeConnect();
    }

    protected function loadConfiguration(): void
    {
        $config = Config::getInstanse();
        $this->driver = $config->getEnv("DB_DRIVER");
        $this->host = $config->getEnv("DB_HOST");
        $this->dbName = $config->getEnv("DB_DATABASE");
        $this->password = $config->getEnv("DB_PASSWORD");
        $this->user = $config->getEnv("DB_USER");
    }

    protected function makeConnect()
    {
        $dsn = "{$this->driver}:dbname={$this->dbName};host={$this->host}";

        $this->connection = new \PDO(
            $dsn,
            $this->user,
            $this->password,
        );
    }
}
