<?php

namespace Bristolian\Model\Types;

use Bristolian\ToArray;

class Meme
{
    use ToArray;

    public function __construct(
        public readonly string $id,
        public readonly string $user_id,
        public readonly string $normalized_name,
        public readonly string $original_filename,
        public readonly string $state,
        public readonly int $size,
        public readonly \DateTimeInterface $created_at,
        public readonly bool $deleted = false,
    ) {
    }
}
