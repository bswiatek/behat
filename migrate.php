<?php

require_once __DIR__ . '/vendor/autoload.php';

$configFileContent = file_get_contents(__DIR__ . '/config/app.json');
$config = json_decode($configFileContent, true);

$schema = new Migrations\Schema($config);

$schema->createSchema();

$version = $schema->getLatestMigration();
echo "Latest version applied is $version.\n";