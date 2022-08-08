<?php

declare(strict_types = 1);

namespace Bristolian\JsonInput;

class InputJsonInput implements JsonInput
{
    public function getData(): array
    {
        $payload = @file_get_contents("php://input");
        if ($payload === false) {
            throw new \Exception("Failed to read php://input");
        }

        return json_decode_safe($payload);
    }
}
