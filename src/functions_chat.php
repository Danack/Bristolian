<?php

declare(strict_types = 1);

use Bristolian\ChatMessage\ChatType;
use Bristolian\Model\Chat\UserChatMessage;
use BristolianChat\ClientHandler;
use Bristolian\Model\Chat\SystemChatMessage;
use Monolog\Logger;




function send_user_message_to_clients(
    UserChatMessage $chat_message,
    Logger          $logger,
    ClientHandler   $clientHandler
): void {

    $data = [
        'type' => ChatType::USER_MESSAGE->value,
        'chat_message' => $chat_message
    ];

    sent_data_to_clients(
        $data,
        $logger,
        $clientHandler
    );
}


function send_system_message_to_clients(
    SystemChatMessage $system_chat_message,
    Logger            $logger,
    ClientHandler     $clientHandler
): void {

    $data = [
        'type' => ChatType::SYSTEM_MESSAGE->value,
        'system_message' => $system_chat_message
    ];

    sent_data_to_clients(
        $data,
        $logger,
        $clientHandler
    );
}



function sent_data_to_clients(
    mixed $data,
    Logger $logger,
    ClientHandler $clientHandler
): void {
    [$error, $values] = convertToValue($data);

    if ($error !== null) {
        $logger->info("error encoding data - $values");
        return;
    }

    $json = json_encode($values);

    if ($json === false || $json === 'null') {
        echo "json is null";
        exit(-1);
    }

    $logger->info("sending message to clients - $json");

    $clientHandler->getGateway()->broadcastText($json)->ignore();
}




/**
 * Generate a fake ChatMessage for testing purposes.
 * IDs start at 1000 and increment with each call.
 * 1 in 5 messages will have a message_reply_id set to one of the previous 10 messages.
 *
 * @return \Bristolian\Model\Chat\UserChatMessage
 */
function generateFakeChatMessage(): \Bristolian\Model\Chat\UserChatMessage
{
    static $currentId = 1000;
    static $recentMessageIds = [];

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

    // 1 in 5 chance to set message_reply_id
    $message_reply_id = null;
    if (mt_rand(1, 5) === 1 && count($recentMessageIds) > 0) {
        // Pick a random message from the last 10 messages
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
