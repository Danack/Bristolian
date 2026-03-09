<?php

namespace BristolianChat;

use Bristolian\Model\Generated\ChatMessage;
use BristolianChat\ClientHandler\ClientHandler;
use BristolianChat\RoomMessagesWatcher\RoomMessagesWatcher;
use Monolog\Logger;

class RoomMessageFetcher
{
    private int|null $previous_id = null;

    public function __construct(
        private readonly RoomMessagesWatcher $roomMessagesWatcher,
        private readonly ClientHandler       $clientHandler,
        private readonly Logger              $logger
    ) {
    }


    private function initialize_previous_id(): void
    {
        $this->previous_id = $this->roomMessagesWatcher->getInitialPreviousId();
    }



    private function getMessage(): ChatMessage|null
    {
        return $this->roomMessagesWatcher->getNextChatMessageAfter($this->previous_id);
    }

    /**
     * Run one poll iteration: fetch next message (if any) and send to clients.
     * Used by run() in a loop; exposed for testing.
     * Initializes previous_id on first call if not yet set.
     */
    public function runOneIteration(): void
    {
        if ($this->previous_id === null) {
            $this->initialize_previous_id();
        }

        try {
            $chat_message = $this->getMessage();

            if ($chat_message !== null) {
                $this->logger->info("Updated previous_id to " . $this->previous_id);
                $this->previous_id = $chat_message->id;

                $this->logger->info("Pulled chat message from MySQL: ");

                send_user_message_to_clients(
                    $chat_message,
                    $this->logger,
                    $this->clientHandler
                );
            }
        } catch (\Throwable $e) {
            $this->logger->error("Exception watching for new messages: " . $e->getMessage());
        }

//        $this->logger->info("RoomMessageFetcher has looped.");
    }

    public function run(): void
    {
        // @codeCoverageIgnoreStart
        $this->initialize_previous_id();

        /* @phpstan-ignore while.alwaysTrue */
        while (true) {
            $this->runOneIteration();
            // TODO - think about rate limiting.
            \Amp\delay(2);
        }
        // @codeCoverageIgnoreEnd
    }
}
