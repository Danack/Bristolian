<?php

namespace Bristolian\Model;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\Parameters\PropertyType\ChatMessageReplyId;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class ChatMessage
{
    public function __construct(
        public readonly int $id,
        public readonly string $user_id,
        public readonly string $room_id,
        public readonly string $text,
        public readonly int $message_reply_id,
        public readonly \DateTimeInterface $created_at,
    ) {
    }
}
