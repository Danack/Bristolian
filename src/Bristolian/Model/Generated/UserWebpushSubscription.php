<?php

declare(strict_types = 1);

// Auto-generated file do not edit

// generated with 'php cli.php generate:model_classes'

namespace Bristolian\Model\Generated;

use Bristolian\FromArray;
use Bristolian\ToString;

class UserWebpushSubscription
{
    use FromArray;
    use ToString;

    public function __construct(
        public readonly int $user_webpush_subscription_id,
        public readonly string $user_id,
        public readonly string $endpoint,
        public readonly string $expiration_time,
        public readonly string $raw,
        public readonly \DateTimeInterface $created_at
    ) {
    }
}
