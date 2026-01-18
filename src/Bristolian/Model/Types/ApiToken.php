<?php

declare(strict_types = 1);

namespace Bristolian\Model\Types;

use Bristolian\ToArray;

class ApiToken
{
    use ToArray;

    public function __construct(
        public readonly string $id,
        public readonly string $token,
        public readonly string $name,
        public readonly \DateTimeInterface $created_at,
        public readonly bool $is_revoked,
        public readonly ?\DateTimeInterface $revoked_at
    ) {
    }
}
