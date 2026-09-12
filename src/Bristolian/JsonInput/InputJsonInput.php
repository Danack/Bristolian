<?php

declare(strict_types = 1);

namespace Bristolian\JsonInput;

use function Safe\file_get_contents;

class InputJsonInput implements JsonInput
{
    /**
     * @codeCoverageIgnore
     * TODO - make a standalone test for this?
     *
     * @return array|mixed[]
     * @throws \Bristolian\Exception\JsonException
     * @throws \JsonException
     * @throws \Seld\JsonLint\ParsingException
     */
    public function getData(): array
    {
        try {
            $payload = file_get_contents("php://input");
        } catch (\Safe\Exceptions\FilesystemException $exception) {
            throw new \Exception("Failed to read php://input", 0, $exception);
        }

        return json_decode_safe($payload);
    }
}
