<?php

declare(strict_types = 1);

namespace UrlFetcher;

class UrlFetcherException extends \Exception
{
    const RESPONSE_NOT_OK = "Response code %s is not ok for uri %s";

    private int $statusCode;
    private string $uri;

    public function __construct(int $statusCode, string $uri, string $message)
    {
        $this->statusCode = $statusCode;
        $this->uri = $uri;

        parent::__construct($message);
    }

    public static function notOk(int $statusCode, string $uri): self
    {
        $message = sprintf(
            self::RESPONSE_NOT_OK,
            $statusCode,
            $uri
        );

        return new self($statusCode, $uri, $message);
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }
}
