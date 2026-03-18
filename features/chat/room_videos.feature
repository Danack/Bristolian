Feature: Room videos panel

  Background:
    Given I am logged in

  Scenario: Videos tab loads room videos panel
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room videos panel to load
    Then I should see "Refresh" on the page
    And I should see "Add video" on the page

  Scenario: Room videos search panel can be used
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room videos panel to load
    And I open the room videos search panel
    Then I should see "Clear" on the page
    And I fill in the room videos search title with "unlikely_title_xyz_12345"
    And I click the "Clear" button in the room videos search form
    Then I should see "Showing" on the page

  Scenario: Add video modal accepts a valid YouTube URL
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room videos panel to load
    And I click the "Add video" button
    And I type a sample YouTube URL in the add video modal
    Then I should see an enabled Continue button in the add video modal
    When I close the add video modal
    And I click the "Refresh" button

  @broken
  Scenario: Add video via preview flow
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room videos panel to load
    And I stub the YouTube IFrame API
    And I click the "Add video" button
    And I type a sample YouTube URL in the add video modal
    And I click Continue in the add video modal
    Then I should see the add video preview form
    And I click the "Add video" button
    Then I should see "Video added" on the page within 10 seconds

  @broken
  Scenario: Add a video clip via preview flow
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room videos panel to load
    And I stub the YouTube IFrame API
    And I click the "Add video" button
    And I type the YouTube URL "https://www.youtube.com/watch?v=9bZkp7q19f0" in the add video modal
    And I click Continue in the add video modal
    Then I should see the add video preview form
    And I fill in the add video clip timestamps start "0:10" and end "0:20"
    And I click the "Add video clip" button
    Then I should see "Showing" on the page within 10 seconds
