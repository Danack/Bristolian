<?php

namespace Bristolian\Model\Types;

class UserWebPushSubscription
{
    private string $endpoint;
    private string $expiration_time;
    private string $raw;


    public function __construct(
        string $endpoint,
        string $expiration_time,
        string $raw
    )
    {
        $this->endpoint = $endpoint;
        $this->expiration_time = $expiration_time;
        $this->raw = $raw;
    }

    /**
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->endpoint;
    }

    /**
     * @return string
     */
    public function getExpirationTime(): string
    {
        return $this->expiration_time;
    }

    /**
     * @return string
     */
    public function getRaw(): string
    {
        return $this->raw;
    }
}
