<?php

declare(strict_types = 1);

use Bristolian\ChatMessage\ChatMessagePayload;
use Bristolian\Model\Chat\UserChatMessage;
use BristolianChat\ClientHandler\ClientHandler;
use Bristolian\Model\Chat\SystemChatMessage;
use Monolog\Logger;

function send_user_message_to_clients(
    UserChatMessage $chat_message,
    Logger          $logger,
    ClientHandler $clientHandler
): void {
    $data = ChatMessagePayload::create_from_user_message($chat_message);

    send_data_to_clients(
        $data,
        $logger,
        $clientHandler
    );
}


function send_system_message_to_clients(
    SystemChatMessage $system_chat_message,
    Logger            $logger,
    ClientHandler $clientHandler
): void {
    $data = ChatMessagePayload::create_from_system_message($system_chat_message);

    send_data_to_clients(
        $data,
        $logger,
        $clientHandler
    );
}



function send_data_to_clients(
    ChatMessagePayload $data,
    Logger $logger,
    ClientHandler $clientHandler
): void {
    [$error, $values] = convertToValue($data->toArray());

    if ($error !== null) {
        // @codeCoverageIgnoreStart
        // convertToValue only errors on unsupported types
        // ChatMessagePayload->toArray() cannot produce these
        $logger->info("error encoding data - $values");
        return;
        // @codeCoverageIgnoreEnd
    }

    $json = json_encode($values);

    if ($json === null) {
        // @codeCoverageIgnoreStart
        $logger->error("Failed to encode data to JSON for type" . $data->type->value);
        return;
        // @codeCoverageIgnoreEnd
    }

    $logger->info("sending message to clients - $json");

    $clientHandler->broadcastText($json);
}




/**
 * Generate a fake ChatMessage for testing purposes.
 * IDs start at 1000 and increment with each call.
 * Every 5th message has message_reply_id set to one of the previous 10 messages.
 *
 * @return \Bristolian\Model\Chat\UserChatMessage
 */
function generateFakeChatMessage(): \Bristolian\Model\Chat\UserChatMessage
{
    static $currentId = 1000;
    static $callCount = 0;
    static $recentMessageIds = [];

    $callCount++;
    $id = $currentId++;

    // Predefined list of user IDs
    $userIds = [
        'user_alice',
        'user_bob',
        'user_charlie',
        'user_diana',
        'user_ethan',
        'user_fiona',
        'user_george',
        'user_hannah',
        'user_ian',
        'user_julia',
    ];

    // Generate random user_id and room_id
    $user_id = $userIds[array_rand($userIds)];
    $room_id = 'room_12345';

    // Generate random text
    $textOptions = [
        'Hello everyone!',
        'How are you doing?',
        'This is a test message.',
        'Anyone there?',
        'What do you think about this?',
        'I agree with that.',
        'Interesting point!',
        'Can you explain more?',
        'Thanks for sharing.',
        'That makes sense.',
        'Great discussion today.',
        'I have a question about that.',
        'Could you elaborate?',
        'That\'s helpful, thanks!',
        'I\'m not sure I follow.',
    ];
    $text = $textOptions[array_rand($textOptions)];

    // Every 5th message is a reply to a previous message
    $message_reply_id = null;
    if ($callCount % 5 === 0 && count($recentMessageIds) > 0) {
        $message_reply_id = $recentMessageIds[array_rand($recentMessageIds)];
    }

    // Generate created_at timestamp
    $created_at = new \DateTimeImmutable();

    // Add this message ID to recent messages
    $recentMessageIds[] = $id;

    // Keep only the last 10 message IDs
    $recentMessageIds = array_slice($recentMessageIds, -10);

    return new \Bristolian\Model\Chat\UserChatMessage(
        $id,
        $user_id,
        $room_id,
        $text,
        $message_reply_id,
        $created_at
    );
}
