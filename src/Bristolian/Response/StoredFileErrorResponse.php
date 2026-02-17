<?php

declare(strict_types = 1);

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

class StoredFileErrorResponse implements StubResponse
{
    /** @var array<string, string>  */
    private $headers;

    /** @var string */
    private $filenameToServe;

    /**
     * @param string $filenameToServe
     * @param array<string, string> $headers
     */
    public function __construct(
        string $filenameToServe,
        array $headers = []
    ) {
        $this->headers = $headers;
        $this->filenameToServe = $filenameToServe;
    }

    public function getStatus() : int
    {
        return 500;
    }

    // if we ever care about not reading the whole file into memory first
    // this function could just emit to output, with appropriate changes in
    // the response mapper
    public function getBody() : string
    {
        // TODO - should wrapping in HTML be done here?
        $contents = sprintf(
            "Failed to retrieve stored file [%s]. Probably contact an administrator.",
            $this->filenameToServe
        );

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
