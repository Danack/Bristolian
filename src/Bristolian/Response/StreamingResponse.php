<?php

namespace Bristolian\Response;

use Bristolian\Exception\BristolianResponseException;
use Psr\Http\Message\StreamInterface;
use SlimDispatcher\Response\ResponseException;
use function Safe\fopen;

class StreamingResponse
{

    /** @var array<string, string>  */
    private $headers;

    /**
     * @var resource
     */
    private $filehandle;

    /** @var string */
    /* @noinspection PhpPropertyOnlyWrittenInspection
     * @phpstan-ignore property.onlyWritten
     */
    private string $filenameToServe;

    /**
     * @param string $filenameToServe
     * @param array<string, string> $headers
     * @throws ResponseException
     */
    public function __construct(
        string $filenameToServe,
        array $headers = []
    ) {
        $standardHeaders = [
            'Content-Type' => getMimeTypeFromFilename($filenameToServe),
        ];

        $this->headers = array_merge($standardHeaders, $headers);

        try {
            $this->filehandle = fopen($filenameToServe, 'r');
        } catch (\Safe\Exceptions\FilesystemException $exception) {
            throw BristolianResponseException::failedToOpenFile($filenameToServe);
        }

        $this->filenameToServe = $filenameToServe;
    }


    public function getStatusCode() : int
    {
        return 200;
    }

    public function getBodyStream() : StreamInterface
    {
        return new \Laminas\Diactoros\Stream($this->filehandle);
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
