<?php

declare(strict_types = 1);

namespace Bristolian\Response;

use Bristolian\Exception\BristolianResponseException;
use SlimDispatcher\Response\StubResponse;
use function Safe\fopen;
use function Safe\rewind;
use function Safe\stream_get_contents;

class BristolianFileResponse implements StubResponse
{
    /** @var array<string, string>  */
    private $headers;

    /**
     * @var resource
     */
    private $filehandle;

    /**
     * @param string $filenameToServe
     * @param array<string, string> $headers
     * @throws BristolianResponseException
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
    }

    public function getStatus() : int
    {
        return 200;
    }

    // if we ever care about not reading the whole file into memory first
    // this function could just emit to output, with appropriate changes in
    // the response mapper
    public function getBody() : string
    {
        rewind($this->filehandle);

        return stream_get_contents($this->filehandle);
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
