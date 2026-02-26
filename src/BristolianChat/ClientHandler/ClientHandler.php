<?php

declare(strict_types=1);

namespace BristolianChat\ClientHandler;

interface ClientHandler
{
    public function broadcastText(string $data, array $excludedClientIds = []): void;
}
