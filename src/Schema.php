<?php

namespace Migrations;

use Exception;
use PDO;

class Schema {
    const SETUP_FILE = __DIR__ . '/../migrations/setup.sql';
    const MIGRATIONS_DIR = __DIR__ . '/../db/migrations/';

    private $config;
    private $connection;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    private function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connection = new PDO(
                "mysql:host={$this->config['host']};" . "dbname={$this->config['schema']}",
                $this->config['user'],
                $this->config['password']
            );
        }

        return $this->connection;
    }

    public function createSchema()
    {
        $this->createDb();

        $conn = $this->getConnection();
        $conn->exec(file_get_contents(self::SETUP_FILE));
    }

    private function createDb()
    {
        $createSchemaCommand = sprintf(
            'mysql -u %s %s -h %s -e "%s"',
            $this->config['user'],
            "-p" . $this->config['password'],
            $this->config['host'],
            "CREATE DATABASE bdd_db_test"
        );
        exec($createSchemaCommand);
    }

    public function getLatestMigration()
    {
        $conn = $this->getConnection();
        $version = $conn->exec("SELECT version FROM migrations ORDER BY 'time' DESC LIMIT 1");

        if ($version === NULL)
        {
            return 0;
        }
        return $version;
    }
}