Feature: Share file link to chat

  Background:
    Given I am logged in

  Scenario: Post to chat button is visible when logged in
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    Then I should see a "Post to chat" button if files exist

  Scenario: Post to chat button is not visible when logged out
    Given I am logged out
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    Then I should not see a "Post to chat" button

  @share @broken
  Scenario: Clicking Post to chat button inserts markdown link into message input
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    And I click the "Post to chat" button if files exist
    Then the message input should contain a markdown link

  @share
  Scenario: Post to chat button inserts text at cursor position
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    And I type "foobar" in the message input
    And I position the cursor after "foo" in the message input
    And I click the "Post to chat" button if files exist
    Then the message input should contain "foo" followed by a markdown link followed by "bar"

