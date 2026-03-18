Feature: Room files panel

  Background:
    Given I am logged in

  Scenario: Files tab loads room files panel
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    Then I should see "Files" on the page
    And I should see "Refresh" on the page

  Scenario: Room files search panel can be opened and cleared
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    And I open the room files search panel
    Then I should see "Clear" on the page
    And I fill in the room files search title with "unlikely_filename_xyz_12345"
    And I click the "Clear" button in the room files search form
    And I close the room files search panel
    Then I should see "Refresh" on the page

  Scenario: Edit tags modal can be opened for a file when files exist
    When I go to "/rooms"
    And I click on the first room link
    And I wait for the room files panel to load
    And I click the "Edit tags" button for the first file if files exist
    Then I should see the edit tags modal
