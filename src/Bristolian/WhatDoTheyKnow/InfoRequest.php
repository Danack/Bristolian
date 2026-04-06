<?php

declare(strict_types=1);

namespace Bristolian\WhatDoTheyKnow;

/**
 * Summary of an information request attached to a request event.
 */
final readonly class InfoRequest
{
    /**
     * @param list<string> $tags
     */
    public function __construct(
        public int $id,
        public string $url_title,
        public string $title,
        public string $created_at,
        public string $updated_at,
        public string $described_state,
        public string $display_status,
        public bool $awaiting_description,
        public string $prominence,
        public string $law_used,
        public array $tags
    ) {
    }
}
