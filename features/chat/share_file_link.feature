Feature: Share file link to chat

  Background:
    Given I am logged in

  Scenario: Share button is visible when logged in
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    Then I should see a "Share" button if files exist

  Scenario: Share button is not visible when not logged out
    Given I am logged out
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    Then I should not see a "Share" button

  @share
  Scenario: Clicking Share button inserts markdown link into message input
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    And I click a Share button if files exist
    Then the message input should contain a markdown link

  @share
  Scenario: Share button inserts text at cursor position
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    And I type "foobar" in the message input
    And I position the cursor after "foo" in the message input
    And I click a Share button if files exist
    Then the message input should contain "foo" followed by a markdown link followed by "bar"

