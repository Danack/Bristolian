<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\Parameters\PropertyType\ChatMessageReplyId;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * The parameters passed by a user when they are sending a message.
 */
class ChatMessageParam implements DataType
{
    use CreateFromVarMap;
    use CreateFromArray;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('text')]
        public readonly string $text,
        #[BasicString('room_id')]
        public readonly string $room_id,
        #[ChatMessageReplyId('message_reply_id')]
        public readonly int|null $message_reply_id,
    ) {
    }
}
