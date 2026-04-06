<?php

declare(strict_types=1);

namespace Bristolian\WhatDoTheyKnow;

/**
 * A single tag on a public authority record (key/value pair from the API).
 */
final readonly class PublicBodyTag
{
    public function __construct(
        public string $key,
        public ?string $value
    ) {
    }
}
