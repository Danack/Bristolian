<?php

namespace Bristolian\Response;

use Bristolian\Exception\BristolianResponseException;
use Psr\Http\Message\StreamInterface;
use SlimDispatcher\Response\ResponseException;

class StreamingResponse
{

    /** @var array<string, string>  */
    private $headers;

    /**
     * @var false|resource
     */
    private $filehandle;

    /** @var string */
    private $filenameToServe;

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

        $this->filehandle = @fopen($filenameToServe, 'r');

        if ($this->filehandle === false) {
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