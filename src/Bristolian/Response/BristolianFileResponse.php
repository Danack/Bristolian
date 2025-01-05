<?php

declare(strict_types=1);

namespace Bristolian\Response;

use Bristolian\Exception\BristolianResponseException;
use SlimDispatcher\Response\ResponseException;
use SlimDispatcher\Response\StubResponse;

class BristolianFileResponse implements StubResponse
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
        $contents = stream_get_contents($this->filehandle);

        // @codeCoverageIgnoreStart
        // I have no idea how to trigger this situation, other than
        // pulling out the hard drive mid-test.
        if ($contents === false) {
            $message = sprintf(
                "Failed to read contents of [%s] from open filehandle.",
                $this->filenameToServe
            );

            throw new ResponseException($message);
        }
        // @codeCoverageIgnoreEnd

        return $contents;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
