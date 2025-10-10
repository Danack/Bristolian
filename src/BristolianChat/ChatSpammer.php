<?php

namespace BristolianChat;

use Bristolian\Model\ChatMessage;
use Bristolian\ChatMessage\ChatType;
use Monolog\Logger;

/**
 * This sits and sends test messages.
 */
class ChatSpammer
{
    public function __construct(
        private readonly ClientHandler $clientHandler,
        private readonly Logger $logger
    ) {
    }

    public function run(): void
    {
        while (true) {
            $chat_message = generateFakeChatMessage();

            send_message_to_clients(
                $chat_message,
                $this->logger,
                $this->clientHandler
            );

//            $data = [
//                'type' => ChatType::MESSAGE->value,
//                'chat_message' => $chat_message
//            ];
//
//            [$error, $values] = convertToValue($data);
//
//            if ($error !== null) {
//                $this->logger->info("error encoding data - $values");
//            }
//
//            $json = json_encode($values);
//
//            if ($json === false || $json === 'null') {
//                echo "json is null";
//                exit(-1);
//            }
//
//            $this->clientHandler->getGateway()->broadcastText($json)->ignore();

            $this->logger->info("message sent - looping");
            // TODO - think about rate limiting.
            \Amp\delay(20); // Wait a bit before retrying
        }
    }
}
