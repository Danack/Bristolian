<?php

namespace Bristolian\Config;

class HardCodedEnvironmentName implements EnvironmentName
{
    public function __construct(public readonly string $env_name)
    {
    }

    public function getEnvironmentNameForEmailSubject(): string
    {
        return $this->env_name;
    }
}
