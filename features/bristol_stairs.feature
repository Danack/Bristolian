Feature: Bristol Stairs Map

  Scenario: Map page loads and displays correctly
    When I go to "/tools/bristol_stairs"
    Then I should see "A map of Bristol Stairs" on the page
    And the page should contain a map element
    And the map should have zoom controls

  Scenario: Upload button is not visible when not logged in
    When I go to "/tools/bristol_stairs"
    Then I should not see a "Upload image" button

  Scenario: Map displays markers when stairs data is available
    When I go to "/tools/bristol_stairs"
    And I wait for the map to load
    Then the map should display markers if stairs are present

  Scenario: Clicking a marker selects a stair
    When I go to "/tools/bristol_stairs"
    And I wait for the map to load
    And I click on a marker if one is present
    Then the URL should change to include a stair ID
    And I should see stair information displayed

  @upload
  Scenario: Upload stair image with 8 steps
    Given I am logged in
    When I go to "/tools/bristol_stairs"
    And I note the current total steps and flights
    And I click the "Upload image" button
    And I upload the file "test/fixtures/stairs/stairs_test_a_8.jpeg" with random GPS coordinates
    Then I should be redirected to a stair detail page
    And I set the steps to 8
    And the stair should have 8 steps
    And the stair should appear on the map
    When I go to "/tools/bristol_stairs"
    Then the total steps should have increased by 8
    And the total flights should have increased by 1

  @upload
  Scenario: Upload stair image with 9 steps
    Given I am logged in
    When I go to "/tools/bristol_stairs"
    And I note the current total steps and flights
    And I click the "Upload image" button
    And I upload the file "test/fixtures/stairs/stairs_test_b_9.jpeg" with random GPS coordinates
    Then I should be redirected to a stair detail page
    And I set the steps to 9
    And the stair should have 9 steps
    And the stair should appear on the map
    When I go to "/tools/bristol_stairs"
    Then the total steps should have increased by 9
    And the total flights should have increased by 1

  @upload
  Scenario: Update stair description
    Given I am logged in
    When I go to "/tools/bristol_stairs"
    And I wait for the map to load
    And I click on a marker if one is present
    Then the URL should change to include a stair ID
    And I set the description to "Test description for Behat test"
    And the stair should have description "Test description for Behat test"

  @upload
  Scenario: Update stair position
    Given I am logged in
    When I go to "/tools/bristol_stairs"
    And I wait for the map to load
    And I click on a marker if one is present
    Then the URL should change to include a stair ID
    And I generate a random position within Bristol
    And I click the "Edit Position" button
    And I move the map to the generated position
    And I click the "Update Position" button
    Then the stair position should be approximately the generated position

  Scenario: GET update endpoint returns error message
    Given I am logged in
    When I navigate to the GET update endpoint for stair ID "test_stair_123"
    Then I should see the error message "This is a GET end point. You probably meant to POST."

