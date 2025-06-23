<?php

namespace Bristolian\Model;

class Email
{
    public function __construct(
        public readonly int $id,
        public readonly string $body,
        public readonly string $recipient,
        public readonly int $retries,
        public readonly string $status,
        public readonly string $subject
    ) {
    }
}
