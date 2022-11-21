Feature: Api Range header pagination handling
  In order to be able to handle pagination
  As a client software developer
  I need to retrieve an API pagination information as Range header

  Background:
    Given I add "Accept" header equal to "application/json"
    And I add "Content-type" header equal to "application/json"

  @loadFixtures
  Scenario: Get the first resources without Range request header
    When I send a "GET" request to "/api/books"
    Then the response status code should be 206
    And the response header "Content-range" should be "books 0-30/100"
    And the response should contain 30 books

  @loadFixtures
  Scenario: Get the first resources with Range request header
    Given I add "Range" header equal to "books=0-10"
    When I send a "GET" request to "/api/books"
    Then the response status code should be 206
    And the response header "Content-range" should be "books 0-10/100"
    And the response should contain 10 books

  @loadFixtures
  Scenario: Error out of range
    Given I add "Range" header equal to "books=99-102"
    When I send a "GET" request to "/api/books"
    Then the response status code should be 416
