<?php

declare(strict_types = 1);

namespace BristolianTest\Support;

use Bristolian\Model\Generated\Room;
use Bristolian\Parameters\CreateUserParams;
use Bristolian\Parameters\SourceLinkParam;
use Bristolian\UploadedFiles\UploadedFile;
use Ramsey\Uuid\Uuid;

/**
 * StandardTestData provides idempotent methods to ensure standard test data exists.
 *
 * Default room world (see docs/refactoring/default_test_scenarios_and_worlds.md):
 * - Standard users (testing@example.com, danack@example.com)
 * - Two rooms: "Housing" and "Off-topic"
 * - Documents in each room; some documents have highlights (source links with highlights_json)
 *
 * Following the pattern from docs/refactoring/testing_scenarios.md:
 * - Methods describe facts about the world, not how the world got there
 * - Each method is idempotent - safe to call multiple times
 * - Methods hide implementation details from tests
 *
 * Future domain-specific scenarios would be things like:
 * - ChatScenario - for chat-specific test scenarios (e.g., withMessages(), withRoomMembers())
 * - RoomScenario - for room-specific test scenarios (e.g., withPrivateRoom(), withPublicRoom())
 * - UserScenario - for user-specific test scenarios (e.g., withAdminUser(), withRegularUser())
 *
 * @coversNothing
 */
final class StandardTestData
{
    private const MIN_DOCUMENTS_PER_ROOM = 2;
    private const MIN_DOCUMENTS_WITH_HIGHLIGHTS_PER_ROOM = 1;

    public function __construct(
        private TestWorld $world
    ) {
    }

    /**
     * Ensure a user exists with the given email and password.
     * If the user already exists, returns the existing user ID.
     * If not, creates the user and returns the new user ID.
     *
     * This is idempotent - safe to call multiple times.
     */
    public function ensureUser(string $email, string $password): string
    {
        $userId = $this->world->findUserByEmail($email);
        if ($userId !== null) {
            return $userId;
        }

        $createUserParams = CreateUserParams::createFromArray([
            'email_address' => $email,
            'password' => $password,
        ]);

        $adminUser = $this->world->adminRepo()->addUser($createUserParams);
        return $adminUser->getUserId();
    }

    /**
     * Ensure a room exists with the given name and purpose.
     * If the room already exists, returns the existing room.
     * If not, creates the room owned by the specified user and returns it.
     *
     * This is idempotent - safe to call multiple times.
     */
    public function ensureRoom(string $name, string $purpose, string $ownerUserId): Room
    {
        $room = $this->world->findRoomByName($name);
        if ($room !== null) {
            return $room;
        }

        return $this->world->roomRepo()->createRoom($ownerUserId, $name, $purpose);
    }

    /**
     * Ensure the standard test setup exists:
     * - testing@example.com and danack@example.com users
     * - "Housing" and "Off-topic" rooms
     * - Documents in each room; some with highlights
     *
     * See docs/refactoring/default_test_scenarios_and_worlds.md.
     */
    public function ensureStandardSetup(): void
    {
        $this->ensureUser('testing@example.com', 'testing');
        $this->ensureUser('danack@example.com', 'testing');

        $housing = $this->getHousingRoom();
        $offTopic = $this->getOffTopicRoom();

        $this->ensureRoomsWithDocuments([$housing, $offTopic]);
    }

    /**
     * Ensure each room has at least some documents, and some of those have highlights.
     * Idempotent: if a room already has enough documents, we skip it.
     *
     * @param array<int, Room> $rooms
     */
    private function ensureRoomsWithDocuments(array $rooms): void
    {
        $userId = $this->getTestingUserId();
        $uploadedFile = UploadedFile::fromFile(__FILE__);

        foreach ($rooms as $room) {
            $files = $this->world->roomFileRepo()->getFilesForRoom($room->id);
            $existing = count($files);
            if ($existing >= self::MIN_DOCUMENTS_PER_ROOM) {
                continue;
            }

            $toAdd = self::MIN_DOCUMENTS_PER_ROOM - $existing;
            $withHighlights = max(0, self::MIN_DOCUMENTS_WITH_HIGHLIGHTS_PER_ROOM - $this->countDocumentsWithHighlightsInRoom($room->id));

            for ($i = 0; $i < $toAdd; $i++) {
                $hasHighlights = $i < $withHighlights;
                $this->addDocumentToRoom($room->id, $userId, $uploadedFile, $hasHighlights);
            }
        }
    }

    private function countDocumentsWithHighlightsInRoom(string $roomId): int
    {
        $links = $this->world->roomSourceLinkRepo()->getSourceLinksForRoom($roomId);
        $withHighlights = 0;
        foreach ($links as $link) {
            if ($link->highlights_json !== '{"highlights": []}' && trim($link->highlights_json) !== '') {
                $withHighlights++;
            }
        }
        return $withHighlights;
    }

    private function addDocumentToRoom(string $roomId, string $userId, UploadedFile $uploadedFile, bool $withHighlights): void
    {
        $normalizedName = Uuid::uuid7()->toString() . '.txt';
        $fileId = $this->world->roomFileObjectInfoRepo()->createRoomFileObjectInfo(
            $userId,
            $normalizedName,
            $uploadedFile
        );
        $this->world->roomFileObjectInfoRepo()->setRoomFileObjectUploaded($fileId);
        $this->world->roomFileRepo()->addFileToRoom($fileId, $roomId);

        if ($withHighlights) {
            $param = SourceLinkParam::createFromArray([
                'title' => 'Standard test doc with highlights placeholder title',
                'highlights_json' => '{"highlights": [{"page": 1, "left": 0, "top": 0, "right": 100, "bottom": 50}]}',
                'text' => 'Highlighted excerpt for standard test data.',
            ]);
            $this->world->roomSourceLinkRepo()->addSourceLink($userId, $roomId, $fileId, $param);
        }
    }

    /**
     * Get the testing user ID.
     * Ensures the user exists first.
     */
    public function getTestingUserId(): string
    {
        return $this->ensureUser('testing@example.com', 'testing');
    }

    /**
     * Get the danack user ID.
     * Ensures the user exists first.
     */
    public function getDanackUserId(): string
    {
        return $this->ensureUser('danack@example.com', 'testing');
    }

    /**
     * Get the Housing room.
     * Ensures the room exists first.
     */
    public function getHousingRoom(): Room
    {
        $testingUserId = $this->getTestingUserId();
        return $this->ensureRoom(
            'Housing',
            'A place to discuss the problem that is BCC housing',
            $testingUserId
        );
    }

    /**
     * Get the Off-topic room.
     * Ensures the room exists first.
     */
    public function getOffTopicRoom(): Room
    {
        $testingUserId = $this->getTestingUserId();
        return $this->ensureRoom(
            'Off-topic',
            'A place to discuss everything else',
            $testingUserId
        );
    }
}
