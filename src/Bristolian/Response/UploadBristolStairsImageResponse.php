<?php

declare(strict_types=1);

namespace Bristolian\Response;

use Bristolian\Exception\DataEncodingException;
use BristolStairInfo;
use SlimDispatcher\Response\StubResponse;

class UploadBristolStairsImageResponse implements StubResponse
{
    private string $body;

    public function __construct(BristolStairInfo $stair_info)
    {
        [$error, $converted_stair_info] = \convertToValue($stair_info);
        if ($error !== null) {
            throw new DataEncodingException("Could not convert stair_info to a value. ", $error);
        }

        $response_ok = [
            'result' => 'success',
            'data' => [
                'stair_info' => $converted_stair_info,
            ],
        ];

        $this->body = json_encode_safe($response_ok, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function getStatus(): int
    {
        return 200;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
        ];
    }

    public function getBody(): string
    {
        return $this->body;
    }
}

