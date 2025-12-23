Feature: Basic

  Scenario: Home page
    When I go to "/"
    Then take a screenshot
    Then print current URL
    Then take a screenshot
    Then I should see "Eldon House music" on the page