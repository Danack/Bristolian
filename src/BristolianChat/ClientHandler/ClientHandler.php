<?php

declare(strict_types=1);

namespace BristolianChat\ClientHandler;

interface ClientHandler
{
    /**
     * @param array<int|string> $excludedClientIds
     */
    public function broadcastText(string $data, array $excludedClientIds = []): void;
}
