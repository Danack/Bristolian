<?php


class Link
{
    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly string $url,
        public readonly string $created_at
    ) {
    }
}
