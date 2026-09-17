<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\RoomLinkRepo;

use PHPUnit\Framework\Attributes\CoversMethod;
use Bristolian\Exception\BristolianException;
use Bristolian\Exception\ContentNotFoundException;
use BristolianGenerated\Model\RoomLink;
use Bristolian\Model\Types\RoomLinkWithUrl;
use Bristolian\Parameters\LinkParam;
use Bristolian\Repo\RoomLinkRepo\RoomLinkRepo;
use BristolianTest\BaseTestCase;
use Bristolian\Repo\LinkRepo\LinkRepo;

/**
 * Abstract test class for RoomLinkRepo implementations.
 *
 * Scenario data (user id, room id) is provided by concrete tests via getValidUserId()
 * and getValidRoomId(). See notes/refactoring/default_test_scenarios_and_worlds.md.
 *
 */

#[CoversMethod(\Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::class, 'addLinkToRoomFromParam')]
#[CoversMethod(\Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::class, 'getRoomLink')]
#[CoversMethod(\Bristolian\Repo\RoomLinkRepo\FakeRoomLinkRepo::class, 'updateTitleAndDescription')]
#[CoversMethod(\Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::class, 'addLinkToRoomFromParam')]
#[CoversMethod(\Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::class, 'getRoomLink')]
#[CoversMethod(\Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo::class, 'updateTitleAndDescription')]

abstract class RoomLinkRepoFixture extends BaseTestCase
{
    /**
     * Get a test instance of the RoomLinkRepo implementation.
     *
     * @return RoomLinkRepo
     */
    abstract public function getTestInstance(LinkRepo $linkRepo): RoomLinkRepo;

    /**
     * LinkRepo instance for building the RoomLinkRepo under test.
     */
    abstract protected function getLinkRepo(): LinkRepo;

    /**
     * A user id that exists in this implementation's world (for FK-safe tests).
     */
    abstract protected function getValidUserId(): string;

    /**
     * A room id that exists in this implementation's world (for FK-safe tests).
     */
    abstract protected function getValidRoomId(): string;

    public function test_addLinkToRoomFromParam(): void
    {
        $repo = $this->getTestInstance($this->getLinkRepo());

        $user_id = $this->getValidUserId();
        $room_id = $this->getValidRoomId();
        $url = 'https://www.example.com/';

        $linkParam = LinkParam::createFromArray([
            'url' => $url
        ]);

        $roomLinkId = $repo->addLinkToRoomFromParam($user_id, $room_id, $linkParam);

        $this->assertNotEmpty($roomLinkId);
    }

    public function test_getLinksForRoom_returns_links_after_adding(): void
    {
        $repo = $this->getTestInstance($this->getLinkRepo());

        $user_id = $this->getValidUserId();
        $room_id = $this->getValidRoomId();
        $url = 'https://www.example.com/';

        $linkParam = LinkParam::createFromArray([
            'url' => $url
        ]);

        $repo->addLinkToRoomFromParam($user_id, $room_id, $linkParam);

        $links = $repo->getLinksForRoom($room_id, \Bristolian\Parameters\RoomContentSearchParams::default());
        $this->assertNotEmpty($links);
    }

    public function test_getRoomLink_returns_null_for_nonexistent_id(): void
    {
        $repo = $this->getTestInstance($this->getLinkRepo());

        try {
            $roomLink = $repo->getRoomLink('nonexistent_id');
            $this->assertNull($roomLink);
        } catch (BristolianException $e) {
            // PdoRoomLinkRepo throws instead of returning null; both are acceptable.
            $this->assertStringContainsString('nonexistent_id', $e->getMessage());
        }
    }

    public function test_getRoomLink_returns_link_after_adding(): void
    {
        $repo = $this->getTestInstance($this->getLinkRepo());

        $user_id = $this->getValidUserId();
        $room_id = $this->getValidRoomId();
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

    public function test_updateTitleAndDescription_updates_fields(): void
    {
        $repo = $this->getTestInstance($this->getLinkRepo());
        $user_id = $this->getValidUserId();
        $room_id = $this->getValidRoomId();
        $roomLinkId = $repo->addLinkToRoomFromParam($user_id, $room_id, LinkParam::createFromArray([
            'url' => 'https://example.com/' . create_test_uniqid(),
            'title' => 'Old title here',
            'description' => 'Old description',
        ]));
        $newTitle = 'New title ' . create_test_uniqid();
        $newDescription = 'New description ' . create_test_uniqid();

        $repo->updateTitleAndDescription($room_id, $roomLinkId, $newTitle, $newDescription);

        $updated = $repo->getRoomLink($roomLinkId);
        $this->assertNotNull($updated);
        $this->assertSame($newTitle, $updated->title);
        $this->assertSame($newDescription, $updated->description);
    }

    public function test_updateTitleAndDescription_throws_when_link_id_unknown(): void
    {
        $repo = $this->getTestInstance($this->getLinkRepo());
        $this->expectException(ContentNotFoundException::class);
        $this->expectExceptionMessage('Link not found in room');
        $repo->updateTitleAndDescription(
            $this->getValidRoomId(),
            '00000000-0000-7000-8000-000000000000',
            'Title',
            'Description'
        );
    }
}
