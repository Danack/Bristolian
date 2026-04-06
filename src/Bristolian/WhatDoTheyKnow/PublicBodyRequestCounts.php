<?php

declare(strict_types=1);

namespace Bristolian\WhatDoTheyKnow;

/**
 * Request statistics embedded under public_body.info in the API payload.
 */
final readonly class PublicBodyRequestCounts
{
    public function __construct(
        public int $requests_count,
        public int $requests_successful_count,
        public int $requests_not_held_count,
        public int $requests_overdue_count,
        public int $requests_visible_classified_count
    ) {
    }
}
