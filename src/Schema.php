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

    public function createSchema() {
        $createSchemaCommand = sprintf(
            'mysql -u %s %s -h %s -e "CREATE SCHEMA IF NOT EXISTS %s"',
            $this->config['user'],
            empty($this->config['password']) ? '' : "-p{$this->config['password']}",
            $this->config['host'],
            $this->config['schema']
        );

        $output = [];
        exec($createSchemaCommand, $output);

        if (!empty($output)) {
            throw new Exception(implode("\n", $output));
        }
    }

    public function getLatestMigration(): int {
        $connection = $this->getConnection();

        $stmt = $connection->query('SELECT 1 FROM migrations');
        if (!$stmt) {
            $connection->exec(file_get_contents(self::SETUP_FILE));
        }

        $query = <<<SQL
SELECT version FROM migrations WHERE status = "success"
ORDER BY version DESC LIMIT 1
SQL;
        return (int) $connection->query($query)->fetch()['version'];
    }

    public function applyMigrationsFrom($version): bool
    {
        $filePath = self::MIGRATIONS_DIR . "$version.sql";

        if (!file_exists($filePath)) {
            return false;
        }

        $connection = $this->getConnection();
        if ($connection->exec(file_get_contents($filePath)) === false) {
            $error = $connection->errorInfo()[2];
            $this->registerMigration($version, 'error');
            throw new Exception($error);
        }

        $this->registerMigration($version, 'success');
        return true;
    }

    private function registerMigration(int $version, string $status)
    {
        $query = <<<SQL
INSERT INTO migrations (version, status)
VALUES (:version, :status)
SQL;
        $params = ['version' => $version, 'status' => $status];
        $this->getConnection()->prepare($query)->execute($params);
    }
}