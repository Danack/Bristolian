<?php

declare(strict_types = 1);

namespace Bristolian\Service;

class RequestNonce
{
    const SIZE_IN_BYTES = 16;

    private string $string;

    public function __construct()
    {
        $bytes = random_bytes(self::SIZE_IN_BYTES);
        $this->string = bin2hex($bytes);
    }

    public function getRandom(): string
    {
        return $this->string;
    }
}
