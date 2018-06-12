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