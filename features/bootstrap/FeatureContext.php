<?php

declare(strict_types=1);

use Behat\MinkExtension\Context\MinkContext;

/**
 * Feature context for Behat browser testing.
 */
class FeatureContext extends MinkContext
{
    /**
     * Checks, that page contains specified text
     * Example: Then I should see "text" on the page
     *
     * @Then /^I should see "([^"]*)" on the page$/
     */
    public function iShouldSeeTextOnThePage(string $text): void
    {
        $this->assertSession()->pageTextContains($this->fixStepArgument($text));
    }
}

