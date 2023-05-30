<?php

namespace Bristolian\Response;

use SlimDispatcher\Response\StubResponse;

class SVGResponse implements StubResponse
{
    /** @var string  */
    private string $body;

    /**
     * @var array<string, string>
     */
    private array $headers = [];

    /** @var int  */
    private $status;

    public function getStatus() : int
    {
        return $this->status;
    }

    /**
     * @return array<string, string>
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * SVGResponse constructor.
     * @param string $xml
     * @param array<string, string> $headers
     */
    public function __construct(string $xml, array $headers = [], int $status = 200)
    {
        $standardHeaders = [
            'Content-Type' => 'image/svg+xml; charset=utf-8'
        ];

        $this->headers = array_merge($standardHeaders, $headers);
        $this->body = $xml;
        $this->status = $status;
    }

    public function getBody() : string
    {
        return $this->body;
    }
}
