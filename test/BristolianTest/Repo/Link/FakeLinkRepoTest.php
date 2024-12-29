<?php

namespace BristolianTest\Repo\Link;

use BristolianTest\BaseTestCase;
use Bristolian\Repo\LinkRepo\FakeLinkRepo;

/**
 * @coversNothing
 */
class FakeLinkRepoTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Repo\LinkRepo\FakeLinkRepo
     */
    public function testWorks()
    {
        $linkRepo = new FakeLinkRepo();

        $user_id = '12345';
        $room_id = '123456';
        $url_1 = "http://www.google.com";
        $url_2 = "http://www.example.com";


        // Check room has no links listed
        $links = $linkRepo->getStoredLinks();
        $this->assertEmpty($links);

        // Check adding link works
        $link_id_1 = $linkRepo->store_link($user_id, $url_1);
        $link_id_2 = $linkRepo->store_link($user_id, $url_2);
        $links = $linkRepo->getStoredLinks();
        $this->assertCount(2, $links);

        $expected_1 = [
            'id' => $link_id_1,
            'user_id' => $user_id,
            'url' => $url_1
        ];
        $expected_2 = [
            'id' => $link_id_2,
            'user_id' => $user_id,
            'url' => $url_2
        ];

        $this->assertSame($expected_1, $links[$link_id_1]);
        $this->assertSame($expected_2, $links[$link_id_2]);
    }
}
