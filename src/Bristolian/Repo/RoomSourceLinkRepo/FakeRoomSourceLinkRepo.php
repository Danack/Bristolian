<?php

declare(strict_types = 1);

namespace Bristolian\Repo\RoomSourceLinkRepo;

use Bristolian\Model\Types\RoomSourceLinkView;
use Bristolian\Parameters\SourceLinkParam;
use Ramsey\Uuid\Uuid;

/**
 * Fake implementation of RoomSourceLinkRepo for testing.
 */
class FakeRoomSourceLinkRepo implements RoomSourceLinkRepo
{
    /**
     * @var array<string, array{id: string, user_id: string, file_id: string, highlights_json: string, text: string}>
     * Keyed by sourcelink_id
     */
    private array $sourceLinks = [];

    /**
     * @var array<string, array{id: string, room_id: string, sourcelink_id: string, title: string}>
     * Keyed by room_sourcelink_id
     */
    private array $roomSourceLinks = [];

    /**
     * @param array<string, array{id: string, room_id: string, sourcelink_id: string, title: string}> $initialRoomSourceLinks
     * @param array<string, array{id: string, user_id: string, file_id: string, highlights_json: string, text: string}> $initialSourceLinks
     */
    public function __construct(
        array $initialRoomSourceLinks = [],
        array $initialSourceLinks = []
    ) {
        $this->roomSourceLinks = $initialRoomSourceLinks;
        $this->sourceLinks = $initialSourceLinks;
    }

    public function addSourceLink(
        string $user_id,
        string $room_id,
        string $file_id,
        SourceLinkParam $sourceLinkParam
    ): string {
        // Create sourcelink
        $uuid = Uuid::uuid7();
        $sourcelink_id = $uuid->toString();

        $this->sourceLinks[$sourcelink_id] = [
            'id' => $sourcelink_id,
            'user_id' => $user_id,
            'file_id' => $file_id,
            'highlights_json' => $sourceLinkParam->highlights_json,
            'text' => $sourceLinkParam->text,
        ];

        // Create room_sourcelink
        $uuid = Uuid::uuid7();
        $room_sourcelink_id = $uuid->toString();

        $this->roomSourceLinks[$room_sourcelink_id] = [
            'id' => $room_sourcelink_id,
            'room_id' => $room_id,
            'sourcelink_id' => $sourcelink_id,
            'title' => $sourceLinkParam->title,
        ];

        return $room_sourcelink_id;
    }

    /**
     * @param string $room_id
     * @return RoomSourceLinkView[]
     */
    public function getSourceLinksForRoom(string $room_id): array
    {
        $results = [];

        foreach ($this->roomSourceLinks as $roomSourceLink) {
            if ($roomSourceLink['room_id'] !== $room_id) {
                continue;
            }

            $sourceLink = $this->sourceLinks[$roomSourceLink['sourcelink_id']] ?? null;
            if ($sourceLink === null) {
                continue;
            }

            $results[] = new RoomSourceLinkView(
                id: $sourceLink['id'],
                user_id: $sourceLink['user_id'],
                file_id: $sourceLink['file_id'],
                highlights_json: $sourceLink['highlights_json'],
                text: $sourceLink['text'],
                title: $roomSourceLink['title'],
                room_sourcelink_id: $roomSourceLink['id'],
            );
        }

        return $results;
    }

    /**
     * @param string $room_id
     * @param string $file_id
     * @return RoomSourceLinkView[]
     */
    public function getSourceLinksForRoomAndFile(
        string $room_id,
        string $file_id,
    ): array {
        $results = [];

        foreach ($this->roomSourceLinks as $roomSourceLink) {
            if ($roomSourceLink['room_id'] !== $room_id) {
                continue;
            }

            $sourceLink = $this->sourceLinks[$roomSourceLink['sourcelink_id']] ?? null;
            if ($sourceLink === null || $sourceLink['file_id'] !== $file_id) {
                continue;
            }

            $results[] = new RoomSourceLinkView(
                id: $sourceLink['id'],
                user_id: $sourceLink['user_id'],
                file_id: $sourceLink['file_id'],
                highlights_json: $sourceLink['highlights_json'],
                text: $sourceLink['text'],
                title: $roomSourceLink['title'],
                room_sourcelink_id: $roomSourceLink['id'],
            );
        }

        return $results;
    }
}
