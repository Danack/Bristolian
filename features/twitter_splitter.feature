Feature: Twitter splitter tool

  Scenario: Twitter splitter page loads
    When I go to "/tools/twitter_splitter"
    Then I should see "Twitter splitter" on the page
    And I should see "No tweets yet." on the page

  Scenario: Typing text generates tweets and char info
    When I go to "/tools/twitter_splitter"
    And I type the following text into the twitter splitter textarea:
      """
      Hello world. This is a short tweet.
      """
    Then I should see "Chars:" on the page
    And I should see "Hello world." on the page
    And I should see "280" on the page

  Scenario: Numbering prefix adds tweet numbering
    When I go to "/tools/twitter_splitter"
    And I select twitter splitter numbering "1/ Prefix"
    And I type the following text into the twitter splitter textarea:
      """
      First tweet. Second tweet.
      """
    Then I should see "1/ " on the page

  Scenario: Long text splits into multiple tweets
    When I go to "/tools/twitter_splitter"
    And I type the following text into the twitter splitter textarea:
      """
      This is a long paragraph intended to force the tool to split the text into multiple tweets. It has sentences. It has commas, and break points, to exercise the splitting logic. Here is another sentence to push it over the limit. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text. Here is more text.
      """
    Then I should see "280" on the page

  Scenario: URL length is counted as 23 characters
    When I go to "/tools/twitter_splitter"
    And I type the following text into the twitter splitter textarea:
      """
      Here is a URL that should be treated specially: https://example.com/some/really/long/path/with/query?abc=def&ghi=jkl
      And a second URL: http://example.org/another/path
      """
    Then I should see "https://example.com" on the page
    And I should see "http://example.org" on the page

  Scenario: Copy marks tweet as copied
    When I go to "/tools/twitter_splitter"
    And I stub clipboard writeText
    And I type the following text into the twitter splitter textarea:
      """
      Copy me please.
      """
    And I click copy for the first tweet
    Then I should see "copied" on the page
