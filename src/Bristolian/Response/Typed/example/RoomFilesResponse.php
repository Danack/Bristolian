<?php

namespace Bristolian\Response\Typed\example;

use Bristolian\Exception\DataEncodingException;
use Bristolian\Model\StoredFile;
use SlimDispatcher\Response\StubResponse;

class RoomFilesResponse implements StubResponse
{
    private string $body;

    /**
     * @var array<string, string>
     */
    private array $headers = [];

    private int $status;

    /**
     * @param StoredFile[] $files
     */
    public function __construct(array $files)
    {
        [$error, $data] = convertToValue($files);

        if ($error !== null) {
            throw new DataEncodingException("Could not convert files to a value. ", $error);
        }

        $response_ok = [
            'result' => 'success',
            'data' => [
                'files' => $data
            ]
        ];

        $this->body = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
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
            'Content-Type' => 'application/json'
        ];
    }

    public function getBody(): string
    {
        return $this->body;
    }
}