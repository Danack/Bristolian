Feature: Room tabs URL anchors

  Background:
    Given I am logged in

  Scenario: Selecting a room tab updates the URL hash
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room tabs panel to load
    And I click the "Files" room tab
    Then the URL hash should be "#files"
    And the "Files" room tab should be selected

  Scenario: Opening a room URL with a hash shows that tab
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room tabs panel to load
    And I click the "Links" room tab
    Then the URL hash should be "#links"
    When I reload the page
    And I wait for the room tabs panel to load
    Then the URL hash should be "#links"
    And the "Links" room tab should be selected
