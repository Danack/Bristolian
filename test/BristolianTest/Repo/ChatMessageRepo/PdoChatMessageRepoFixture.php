<?php

declare(strict_types = 1);

namespace BristolianTest\Repo\ChatMessageRepo;

use Bristolian\Repo\ChatMessageRepo\ChatMessageRepo;
use Bristolian\Repo\ChatMessageRepo\PdoChatMessageRepo;

/**
 * @group db
 */
class PdoChatMessageRepoFixture extends ChatMessageRepoFixture
{
    public function getTestInstance(): ChatMessageRepo
    {
        return $this->injector->make(PdoChatMessageRepo::class);
    }

    protected function getTestUserId(): string
    {
        $adminUser = $this->createTestAdminUser();
        return $adminUser->getUserId();
    }

    protected function getTestRoomId(): string
    {
        [$room, $user] = $this->createTestUserAndRoom();
        return $room->id;
    }
}
