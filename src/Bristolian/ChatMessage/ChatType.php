<?php

namespace Bristolian\ChatMessage;

/**
 * The different types of chat messages. TBH not sure this division is correct.
 */
enum ChatType: string
{
    case USER_MESSAGE = 'message';
    case SYSTEM_MESSAGE = 'system_message';
    case MESSAGE_DELETED = 'message_deleted';
}
