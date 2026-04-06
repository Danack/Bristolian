<?php

declare(strict_types=1);

namespace Bristolian\WhatDoTheyKnow;

/**
 * Public authority (body) attached to a request event.
 */
final readonly class PublicBody
{
    /**
     * @param list<PublicBodyTag> $tags
     */
    public function __construct(
        public int $id,
        public string $url_name,
        public string $name,
        public ?string $short_name,
        public string $created_at,
        public string $updated_at,
        public string $home_page,
        public string $notes,
        public string $publication_scheme,
        public string $disclosure_log,
        public array $tags,
        public PublicBodyRequestCounts $request_counts
    ) {
    }
}
