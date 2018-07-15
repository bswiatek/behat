<?php

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\SnippetAcceptingContext;

require_once __DIR__ . '/../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    private $db;
    private $config;
    private $output;

    public function __construct()
    {
        $configFileContent = file_get_contents(
            __DIR__ . '/../../config/app.json'
        );
        $this->config = json_decode($configFileContent, true);
    }

    private function getDb(): PDO{
        if ($this->db === null) {
            $this->db = new PDO(
                "mysql:host={$this->config['host']}; dbname=bdd_db_test",
                $this->config['user'],
                $this->config['password']
            );
        }

        return $this->db;
    }

    /**
     * @Given I do not have the :arg1 schema
     */
    public function iDoNotHaveTheSchema()
    {
        $this->executeQuery('DROP SCHEMA IF EXISTS bdd_db_test');
    }

    /**
     * @Given I do not have migrations files
     */
    public function iDoNotHaveMigrationsFiles()
    {
        exec('rm db/migrations/*.sql > /dev/null 2>&1');
    }

    /**
     * @When I run the migrations script
     */
    public function iRunTheMigrationsScript()
    {
        exec('php migrate.php', $this->output);
    }

    /**
     * @Then I should have an empty migrations table
     */
    public function iShouldHaveAnEmptyMigrationsTable()
    {
        $migrations = $this->getDb()
            ->query('SELECT * FROM migrations')
            ->fetch();
        assertEmpty($migrations);
    }

    /**
     * @Then I should get:
     */
    public function iShouldGet(PyStringNode $string)
    {
        assertEquals(implode("\n", $this->output), $string);
    }

    private function executeQuery(string $query)
    {
        $removeSchemaCommand = sprintf(
            'mysql -u %s %s -h %s -e "%s"',
            $this->config['user'],
            "-p" . $this->config['password'],
            $this->config['host'],
            $query
        );
        exec($removeSchemaCommand);
    }
}
