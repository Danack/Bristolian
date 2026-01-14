<?php

namespace Bristolian\Model\Types;

/**
 * An email that has arrived and been stored in the database.
 */
class IncomingEmail
{
    public function __construct(
        public readonly int $id,
        public readonly string $message_id,
        public readonly string $body_plain,
        public readonly string $provider_variables,
        public readonly string $raw_email,
        public readonly string $recipient,
        public readonly string $retries,
        public readonly string $sender,
        public readonly string $status,
        public readonly string $stripped_text,
        public readonly string $subject,
        public readonly \DateTimeInterface $created_at,
        public readonly \DateTimeInterface $updated_at
    ) {
    }
}
