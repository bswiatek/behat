Feature: Setup
  In order to run database migrations
  As a developer
  I need to be able to create the empty schema and migrations table.

  Background:
    Given I do not have the "bdd_db_test" schema

  Scenario: Schema does not exists and I do not have migrations
    Given I do not have the "bdd_db_test" schema
    And I do not have migrations files
    When I run the migrations script
    Then I should have an empty migrations table
    And I should get:
      """
      Latest version applied is 0.
      """

  Scenario: Schema does not exists and I have migrations
    Given I have migration file 1:
    """
    CREATE TABLE test1(id INT);
    """
    And I have migration file 2:
    """
    CREATE TABLE test2(id INT);
    """
    When I run the migrations script
    Then I should only have the following tables:
      | migrations |
      | test1      |
      | test2      |
    And I should have the following migrations:
      | 1 | success |
      | 2 | success |
    And I should get:
      """
      Latest version applied is 0.
      Applied migration 1 successfully.
      Applied migration 2 successfully.
      """