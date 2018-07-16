<?php

require_once __DIR__ . '/vendor/autoload.php';

$configFileContent = file_get_contents(__DIR__ . '/config/app.json');
$config = json_decode($configFileContent, true);

$schema = new Migrations\Schema($config);

$schema->createSchema();

$version = $schema->getLatestMigration();
echo "Latest version applied is $version.\n";

do {
    $version++;

    try {
        $result = $schema->applyMigrationsFrom($version);
        if ($result) {
            echo "Applied migration $version successfully.\n";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        echo "Error applying migration $version: $error.\n";
        exit(1);
    }
} while ($result);