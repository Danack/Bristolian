<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomLinkRepo;

use Bristolian\Model\Generated\RoomLink;
use Bristolian\Parameters\LinkParam;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use BristolianTest\BaseTestCase;
use Bristolian\Repo\LinkRepo\LinkRepo;

/**
 * Abstract test class for RoomLinkRepo implementations.
 */
abstract class RoomLinkRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the RoomLinkRepo implementation.
     *
     * @return RoomLinkRepo
     */
    abstract public function getTestInstance(LinkRepo $linkRepo): RoomLinkRepo;

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\RoomLinkRepo::addLinkToRoomFromParam
     */
    public function test_addLinkToRoomFromParam(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $room_id = 'room_456';
        $url = 'https://www.example.com/';

        $linkParam = LinkParam::createFromArray([
            'url' => $url
        ]);

        $roomLinkId = $repo->addLinkToRoomFromParam($user_id, $room_id, $linkParam);

        $this->assertNotEmpty($roomLinkId);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\RoomLinkRepo::getLinksForRoom
     * @covers \Bristolian\Repo\RoomLinkRepo\RoomLinkRepo::addLinkToRoomFromParam
     */
    public function test_getLinksForRoom_returns_links_after_adding(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $room_id = 'room_456';
        $url = 'https://www.example.com/';

        $linkParam = LinkParam::createFromArray([
            'url' => $url
        ]);

        $repo->addLinkToRoomFromParam($user_id, $room_id, $linkParam);

        $links = $repo->getLinksForRoom($room_id);
        $this->assertNotEmpty($links);
        $this->assertContainsOnlyInstancesOf(RoomLink::class, $links);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\RoomLinkRepo::getRoomLink
     */
    public function test_getRoomLink_returns_null_for_nonexistent_id(): void
    {
        $repo = $this->getTestInstance();

        $roomLink = $repo->getRoomLink('nonexistent_id');
        $this->assertNull($roomLink);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\RoomLinkRepo::getRoomLink
     * @covers \Bristolian\Repo\RoomLinkRepo\RoomLinkRepo::addLinkToRoomFromParam
     */
    public function test_getRoomLink_returns_link_after_adding(): void
    {
        $repo = $this->getTestInstance();

        $user_id = 'user_123';
        $room_id = 'room_456';
        $url = 'https://www.example.com/';

        $linkParam = LinkParam::createFromArray([
            'url' => $url
        ]);

        $roomLinkId = $repo->addLinkToRoomFromParam($user_id, $room_id, $linkParam);

        $roomLink = $repo->getRoomLink($roomLinkId);
        $this->assertNotNull($roomLink);
        $this->assertInstanceOf(RoomLink::class, $roomLink);
        $this->assertSame($room_id, $roomLink->room_id);
    }
}
