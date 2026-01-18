<?php

namespace Bristolian\Model\Types;

use Bristolian\ToArray;

class UserDisplayName
{
    use ToArray;

    public function __construct(
        public readonly int $id,
        public readonly string $user_id,
        public readonly string $display_name,
        public readonly int $version,
        public readonly \DateTimeInterface $created_at
    ) {
    }
}
