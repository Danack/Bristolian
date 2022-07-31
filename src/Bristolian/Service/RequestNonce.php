<?php

declare(strict_types = 1);

namespace Bristolian\Service;

class RequestNonce
{
    private string $string;

    public function __construct()
    {
        $bytes = random_bytes(12);
        $this->string = bin2hex($bytes);
    }

    public function getRandom(): string
    {
        return $this->string;
    }
}
