<?php

namespace Bristolian\Repo\UserSearch;

class InMemoryUserSearch implements UserSearch
{
    /** @var string[] */
    private array $emailAddresses = [];

    /**
     * @param string[] $emailAddresses seed data
     */
    public function __construct(array $emailAddresses = [])
    {
        $this->emailAddresses = $emailAddresses;
    }

    public function addEmailAddress(string $emailAddress): void
    {
        $this->emailAddresses[] = $emailAddress;
    }

    /**
     * @return string[]
     */
    public function searchUsernamesByPrefix(string $username_prefix): array
    {
        $results = [];
        foreach ($this->emailAddresses as $emailAddress) {
            if (str_starts_with($emailAddress, $username_prefix)) {
                $results[] = $emailAddress;
            }
            if (count($results) >= UserSearch::MAX_SEARCH_RESULTS) {
                break;
            }
        }
        return $results;
    }
}
