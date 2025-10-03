<?php

namespace Bristolian\Service\RoomMessageService;

use Bristolian\Keys\RoomMessageKey;
use Bristolian\ChatMessage\StandardMessage;
use Redis;

class RedisRoomMessageService implements RoomMessageService
{
    public function __construct(
        private readonly Redis $redis,
    ) {
    }

    public function sendMessage(StandardMessage $message): void
    {
        $this->redis->rPush(
            RoomMessageKey::getAbsoluteKeyName(),
            $message->toString()
        );
    }
}
