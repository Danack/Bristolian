<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\Link;

use Bristolian\Repo\LinkRepo\LinkRepo;
use BristolianTest\BaseTestCase;

/**
 * Abstract test class for LinkRepo implementations.
 *
 * @coversNothing
 */
abstract class LinkRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the LinkRepo implementation.
     *
     * @return LinkRepo
     */
    abstract public function getTestInstance(): LinkRepo;


    /**
     * @covers \Bristolian\Repo\LinkRepo\LinkRepo::store_link
     * @covers \Bristolian\Repo\LinkRepo\FakeLinkRepo::store_link
     * @covers \Bristolian\Repo\LinkRepo\PdoLinkRepo::__construct
     * @covers \Bristolian\Repo\LinkRepo\PdoLinkRepo::store_link
     */
    public function test_store_link(): void
    {
        $linkRepo = $this->getTestInstance();

        $user_id = '12345';
        $url_1 = "http://www.google.com";

        $link_id_1 = $linkRepo->store_link($user_id, $url_1);
        $this->assertNotEmpty($link_id_1);
    }


    /**
     * @covers \Bristolian\Repo\LinkRepo\LinkRepo::store_link
     * @covers \Bristolian\Repo\LinkRepo\FakeLinkRepo::store_link
     * @covers \Bristolian\Repo\LinkRepo\PdoLinkRepo::store_link
     */
    public function test_store_link_returns_different_ids_for_different_calls(): void
    {
        $linkRepo = $this->getTestInstance();

        $user_id = '12345';
        $url_1 = "http://www.google.com";
        $url_2 = "http://www.example.com";

        $link_id_1 = $linkRepo->store_link($user_id, $url_1);
        $link_id_2 = $linkRepo->store_link($user_id, $url_2);

        $this->assertNotSame($link_id_1, $link_id_2);
    }
}
