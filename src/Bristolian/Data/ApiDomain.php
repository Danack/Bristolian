<?php

declare(strict_types = 1);

namespace Bristolian\Data;

class ApiDomain
{
    /** @var string */
    private string $domain;

    /**
     * @param string $domain
     */
    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }
}
