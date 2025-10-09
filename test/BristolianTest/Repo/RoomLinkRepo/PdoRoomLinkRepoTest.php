<?php

namespace BristolianTest\Repo\RoomLinkRepo;

use Bristolian\Exception\BristolianException;
use Bristolian\Parameters\LinkParam;
use Bristolian\Model\RoomLink;
use Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo;
use BristolianTest\BaseTestCase;
use BristolianTest\Repo\TestPlaceholders;

class PdoRoomLinkRepoTest extends BaseTestCase
{
    use TestPlaceholders;

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testAddLinkToRoom()
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        $url = $this->getTestLink();

        $room_link_id = $roomLinkRepo->addLinkToRoomFromParam(
            $user->getUserId(),
            $room->getRoomId(),
            LinkParam::createFromArray([
                'url' => $url
            ])
        );

        $room_link = $roomLinkRepo->getRoomLink($room_link_id);
        $this->assertSame($room_link_id, $room_link->id);
        $this->assertSame($url, $room_link->url);
        $this->assertNull($room_link->title);
        $this->assertNull($room_link->description);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testAddLinkToRoomWithTitleAndDescription()
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        $url = $this->getTestLink();
        $title = 'Test Link Title';
        $description = 'Test Link Description';

        $room_link_id = $roomLinkRepo->addLinkToRoomFromParam(
            $user->getUserId(),
            $room->getRoomId(),
            LinkParam::createFromArray([
                'url' => $url,
                'title' => $title,
                'description' => $description
            ])
        );

        $room_link = $roomLinkRepo->getRoomLink($room_link_id);
        $this->assertSame($room_link_id, $room_link->id);
        $this->assertSame($url, $room_link->url);
        $this->assertSame($title, $room_link->title);
        $this->assertSame($description, $room_link->description);
        $this->assertSame($room->getRoomId(), $room_link->room_id);
        $this->assertSame($user->getUserId(), $room_link->user_id);
        $this->assertInstanceOf(\DateTimeInterface::class, $room_link->created_at);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testGetLinksForRoom()
    {
        $this->initPdoTestObjects();
        [$room, $user] = $this->createTestUserAndRoom();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        // Initially room should have no links
        $roomLinks = $roomLinkRepo->getLinksForRoom($room->getRoomId());
        $this->assertEmpty($roomLinks);

        // Add first link
        $url1 = $this->getTestLink();
        $title1 = 'First Link';
        $room_link_id_1 = $roomLinkRepo->addLinkToRoomFromParam(
            $user->getUserId(),
            $room->getRoomId(),
            LinkParam::createFromArray([
                'url' => $url1,
                'title' => $title1
            ])
        );

        // Add second link
        $url2 = $this->getTestLink();
        $title2 = 'Second Link';
        $room_link_id_2 = $roomLinkRepo->addLinkToRoomFromParam(
            $user->getUserId(),
            $room->getRoomId(),
            LinkParam::createFromArray([
                'url' => $url2,
                'title' => $title2
            ])
        );

        // Retrieve all links for the room
        $roomLinks = $roomLinkRepo->getLinksForRoom($room->getRoomId());

        $this->assertCount(2, $roomLinks);
        $this->assertContainsOnlyInstancesOf(RoomLink::class, $roomLinks);

        // Verify first link
        $link1 = array_filter($roomLinks, fn($link) => $link->id === $room_link_id_1);
        $link1 = array_values($link1)[0];
        $this->assertSame($url1, $link1->url);
        $this->assertSame($title1, $link1->title);

        // Verify second link
        $link2 = array_filter($roomLinks, fn($link) => $link->id === $room_link_id_2);
        $link2 = array_values($link2)[0];
        $this->assertSame($url2, $link2->url);
        $this->assertSame($title2, $link2->title);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testGetLinksForRoomReturnsEmptyArrayForNonExistentRoom()
    {
        $this->initPdoTestObjects();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        // Try to get links for a room that doesn't exist
        $roomLinks = $roomLinkRepo->getLinksForRoom('non-existent-room-id');

        $this->assertIsArray($roomLinks);
        $this->assertEmpty($roomLinks);
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testGetRoomLinkThrowsExceptionForNonExistentLink()
    {
        $this->initPdoTestObjects();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        $this->expectException(BristolianException::class);
        $this->expectExceptionMessage("Failed to find room link with 'id' => non-existent-link-id");

        $roomLinkRepo->getRoomLink('non-existent-link-id');
    }

    /**
     * @covers \Bristolian\Repo\RoomLinkRepo\PdoRoomLinkRepo
     */
    public function testMultipleLinksFromDifferentUsers()
    {
        $this->initPdoTestObjects();
        [$room, $user1] = $this->createTestUserAndRoom();
        $user2 = $this->createTestAdminUser();
        $roomLinkRepo = $this->injector->make(PdoRoomLinkRepo::class);

        // Add link from first user
        $url1 = $this->getTestLink();
        $room_link_id_1 = $roomLinkRepo->addLinkToRoomFromParam(
            $user1->getUserId(),
            $room->getRoomId(),
            LinkParam::createFromArray(['url' => $url1])
        );

        // Add link from second user
        $url2 = $this->getTestLink();
        $room_link_id_2 = $roomLinkRepo->addLinkToRoomFromParam(
            $user2->getUserId(),
            $room->getRoomId(),
            LinkParam::createFromArray(['url' => $url2])
        );

        // Retrieve both links
        $link1 = $roomLinkRepo->getRoomLink($room_link_id_1);
        $link2 = $roomLinkRepo->getRoomLink($room_link_id_2);

        // Verify user IDs are correct
        $this->assertSame($user1->getUserId(), $link1->user_id);
        $this->assertSame($user2->getUserId(), $link2->user_id);

        // Both links should be in the same room
        $this->assertSame($room->getRoomId(), $link1->room_id);
        $this->assertSame($room->getRoomId(), $link2->room_id);

        // Verify both appear in the room's links
        $roomLinks = $roomLinkRepo->getLinksForRoom($room->getRoomId());
        $this->assertCount(2, $roomLinks);
    }
}
