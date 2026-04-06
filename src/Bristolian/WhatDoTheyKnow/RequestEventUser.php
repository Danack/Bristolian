<?php

declare(strict_types=1);

namespace Bristolian\WhatDoTheyKnow;

/**
 * User (requester) attached to a request event.
 */
final readonly class RequestEventUser
{
    public function __construct(
        public int $id,
        public string $url_name,
        public string $name,
        public string $ban_text,
        public string $about_me
    ) {
    }
}
