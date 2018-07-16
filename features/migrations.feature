Feature: Migrations
  In order to add changes to my database schema
  As a developer
  I need to be able to run the migrations script

  Background:
    Given I have the bdd_db_test

  Scenario: Migrations are not consecutive
    Given I have migration 3
    And I have migration file 4:
      """
      CREATE TABLE test4(id INT);
      """
    And I have migration file 6:
      """
      CREATE TABLE test6(id INT);
      """
    When I run the migrations script
    Then I should only have the following tables:
      | migrations |
      | test4      |
    And I should have the following migrations:
      | 3 | success |
      | 4 | success |
    And I should get:
      """
      Latest version applied is 3.
      Applied migration 4 successfully.
      """

  Scenario: A migration throws an error
    Given I have migration file 1:
      """
      CREATE TABLE test1(id INT);
      """
    And I have migration file 2:
      """
      CREATE TABLE test1(id INT);
      """
    And I have migration file 3:
      """
      CREATE TABLE test3(id INT);
      """
    When I run the migrations script
    Then I should only have the following tables:
      | migrations |
      | test1      |
    And I should have the following migrations:
      | 1 | success |
      | 2 | error   |
    And I should get:
      """
      Latest version applied is 0.
      Applied migration 1 successfully.
      Error applying migration 2: Table 'test1' already exists.
      """