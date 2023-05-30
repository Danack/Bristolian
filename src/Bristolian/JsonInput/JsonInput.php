<?php

declare(strict_types = 1);

namespace Bristolian\JsonInput;

interface JsonInput
{
    /**
     * @return mixed[]
     */
    public function getData(): array;
}
