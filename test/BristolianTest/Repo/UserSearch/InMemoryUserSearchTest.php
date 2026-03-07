<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\UserSearch;

use Bristolian\Repo\UserSearch\InMemoryUserSearch;
use Bristolian\Repo\UserSearch\UserSearch;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Bristolian\Repo\UserSearch\InMemoryUserSearch
 * @group standard_repo
 */
class InMemoryUserSearchTest extends TestCase
{
    public function test_empty_repo_returns_no_results(): void
    {
        $repo = new InMemoryUserSearch();

        $results = $repo->searchUsernamesByPrefix('alice');

        $this->assertSame([], $results);
    }

    public function test_search_matches_prefix(): void
    {
        $repo = new InMemoryUserSearch([
            'alice@example.com',
            'bob@example.com',
            'alex@example.com',
        ]);

        $results = $repo->searchUsernamesByPrefix('al');

        $this->assertSame(['alice@example.com', 'alex@example.com'], $results);
    }

    public function test_search_is_case_sensitive(): void
    {
        $repo = new InMemoryUserSearch([
            'Alice@example.com',
            'alice@example.com',
        ]);

        $results = $repo->searchUsernamesByPrefix('alice');

        $this->assertSame(['alice@example.com'], $results);
    }

    public function test_search_returns_no_match_for_unmatched_prefix(): void
    {
        $repo = new InMemoryUserSearch([
            'alice@example.com',
            'bob@example.com',
        ]);

        $results = $repo->searchUsernamesByPrefix('charlie');

        $this->assertSame([], $results);
    }

    public function test_search_respects_max_results_limit(): void
    {
        $addresses = [];
        for ($i = 0; $i < UserSearch::MAX_SEARCH_RESULTS + 10; $i++) {
            $addresses[] = "user{$i}@example.com";
        }
        $repo = new InMemoryUserSearch($addresses);

        $results = $repo->searchUsernamesByPrefix('user');

        $this->assertCount(UserSearch::MAX_SEARCH_RESULTS, $results);
    }

    public function test_addEmailAddress_makes_address_searchable(): void
    {
        $repo = new InMemoryUserSearch();

        $repo->addEmailAddress('new@example.com');

        $results = $repo->searchUsernamesByPrefix('new');

        $this->assertSame(['new@example.com'], $results);
    }

    public function test_constructor_seeds_data(): void
    {
        $repo = new InMemoryUserSearch(['seed@example.com']);

        $results = $repo->searchUsernamesByPrefix('seed');

        $this->assertSame(['seed@example.com'], $results);
    }

    public function test_empty_prefix_matches_all(): void
    {
        $repo = new InMemoryUserSearch([
            'alice@example.com',
            'bob@example.com',
        ]);

        $results = $repo->searchUsernamesByPrefix('');

        $this->assertCount(2, $results);
    }
}
