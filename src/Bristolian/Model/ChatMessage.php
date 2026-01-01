<?php

namespace Bristolian\Model;

use Bristolian\FromArray;
use Bristolian\FromString;
use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\Parameters\PropertyType\ChatMessageReplyId;
use Bristolian\ToString;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class ChatMessage
{
    use ToString;
    use FromString;


    public function __construct(
        public readonly int $id,
        public readonly string $user_id,
        public readonly string $room_id,
        public readonly string $text,
        public readonly int|null $reply_message_id,
        public readonly \DateTimeInterface $created_at,
    ) {
    }
}
