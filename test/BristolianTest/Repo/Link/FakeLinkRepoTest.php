<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\Link;

use Bristolian\Repo\LinkRepo\FakeLinkRepo;
use Bristolian\Repo\LinkRepo\LinkRepo;

/**
 * @group standard_repo
 * @coversNothing
 */
class FakeLinkRepoTest extends LinkRepoFixture
{
    /**
     * @return LinkRepo
     */
    public function getTestInstance(): LinkRepo
    {
        return new FakeLinkRepo();
    }

    /**
     * Test FakeLinkRepo-specific method getStoredLinks
     *
     * @covers \Bristolian\Repo\LinkRepo\FakeLinkRepo::getStoredLinks
     */
    public function test_getStoredLinks(): void
    {
        $linkRepo = new FakeLinkRepo();

        $user_id = '12345';
        $url_1 = "http://www.google.com";
        $url_2 = "http://www.example.com";

        // Check room has no links listed initially
        $links = $linkRepo->getStoredLinks();
        $this->assertEmpty($links);

        // Check adding link works
        $link_id_1 = $linkRepo->store_link($user_id, $url_1);
        $link_id_2 = $linkRepo->store_link($user_id, $url_2);
        $links = $linkRepo->getStoredLinks();
        $this->assertCount(2, $links);

        $this->assertSame($link_id_1, $links[$link_id_1]->id);
        $this->assertSame($user_id, $links[$link_id_1]->user_id);
        $this->assertSame($url_1, $links[$link_id_1]->url);

        $this->assertSame($link_id_2, $links[$link_id_2]->id);
        $this->assertSame($user_id, $links[$link_id_2]->user_id);
        $this->assertSame($url_2, $links[$link_id_2]->url);
    }

    /**
     * @covers \Bristolian\Repo\LinkRepo\FakeLinkRepo::getLastAddedLink
     */
    public function test_getLastAddedLink_returns_null_when_empty(): void
    {
        $linkRepo = new FakeLinkRepo();
        $this->assertNull($linkRepo->getLastAddedLink());
    }

    /**
     * @covers \Bristolian\Repo\LinkRepo\FakeLinkRepo::getLastAddedLink
     */
    public function test_getLastAddedLink_returns_last_added_link(): void
    {
        $linkRepo = new FakeLinkRepo();
        $linkRepo->store_link('user1', 'https://example.com/first');
        $linkRepo->store_link('user1', 'https://example.com/second');
        $last = $linkRepo->getLastAddedLink();
        $this->assertNotNull($last);
        $this->assertSame('https://example.com/second', $last->url);
    }
}
