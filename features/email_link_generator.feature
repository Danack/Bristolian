Feature: Email link generator

  Scenario: Email link generator page loads
    When I go to "/tools/email_link_generator"
    Then I should see "Input" on the page
    And I should see "Output" on the page
    And I should see "Address" on the page

  Scenario: Filling address produces mailto link in output
    When I go to "/tools/email_link_generator"
    And I fill in "email_link_address" with "test@example.com"
    Then I should see "mailto:test@example.com" on the page
    And I should see "test@example.com" on the page

  Scenario: Link text can be customised
    When I go to "/tools/email_link_generator"
    And I fill in "email_link_address" with "contact@site.org"
    And I fill in "email_link_link_text" with "Contact us"
    Then I should see "mailto:contact@site.org" on the page
    And I should see "Contact us" on the page

  Scenario: Subject is included in generated link
    When I go to "/tools/email_link_generator"
    And I fill in "email_link_address" with "a@b.co"
    And I fill in "email_link_subject" with "Hello world"
    Then I should see "Subject=Hello%20world" on the page
    And I should see "mailto:a@b.co" on the page

  Scenario: CC is included in generated link
    When I go to "/tools/email_link_generator"
    And I fill in "email_link_address" with "to@example.com"
    And I fill in "email_link_cc" with "cc@example.com"
    Then I should see "cc=cc%40example.com" on the page
    And I should see "mailto:to@example.com" on the page

  Scenario: BCC is included in generated link
    When I go to "/tools/email_link_generator"
    And I fill in "email_link_address" with "to@example.com"
    And I fill in "email_link_bcc" with "bcc@example.com"
    Then I should see "bcc=bcc%40example.com" on the page
    And I should see "mailto:to@example.com" on the page

  Scenario: Body is included in generated link
    When I go to "/tools/email_link_generator"
    And I fill in "email_link_address" with "recipient@test.org"
    And I fill in "email_link_body" with "Email body text"
    Then I should see "body=Email%20body%20text" on the page
    And I should see "mailto:recipient@test.org" on the page

  Scenario: Full form produces complete mailto link
    When I go to "/tools/email_link_generator"
    And I fill in "email_link_address" with "full@test.com"
    And I fill in "email_link_link_text" with "Send email"
    And I fill in "email_link_subject" with "Test subject"
    And I fill in "email_link_cc" with "cc@test.com"
    And I fill in "email_link_bcc" with "bcc@test.com"
    And I fill in "email_link_body" with "Message body"
    Then I should see "mailto:full@test.com" on the page
    And I should see "Send email" on the page
    And I should see "Subject=Test%20subject" on the page
    And I should see "cc=cc%40test.com" on the page
    And I should see "bcc=bcc%40test.com" on the page
    And I should see "body=Message%20body" on the page

  @wip
  Scenario: Copy button copies link to clipboard when available
    When I go to "/tools/email_link_generator"
    And I fill in "email_link_address" with "copy@test.com"
    And I click the "Copy" button if it is present
    Then I should see "mailto:copy@test.com" on the page
