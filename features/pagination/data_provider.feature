Feature: Api Range header pagination handling
  In order to be able to handle pagination
  As a client software developer
  I need to retrieve an API pagination information as Range header

  Background:
    Given I add "Accept" header equal to "application/json"
    And I add "Content-type" header equal to "application/json"

  @loadFixtures
  Scenario: Get dummies resources provider with custom provider
    When I send a "GET" request to "/api/dummies"
    Then the response status code should be 200

  @loadFixtures
  Scenario: Get dummies resources provider with custom provider
    Given I add "Range" header equal to "dummies=0-10"
    When I send a "GET" request to "/api/dummies"
    Then the response status code should be 206
    And the response header "Content-range" should be "dummies 0-10/100"
    And the response should contain 10 dummies

  @loadFixtures
  Scenario: Error out of range
    Given I add "Range" header equal to "dummies=1000-1002"
    When I send a "GET" request to "/api/dummies"
    Then the response status code should be 416
