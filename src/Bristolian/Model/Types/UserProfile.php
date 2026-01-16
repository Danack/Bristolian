<?php

namespace Bristolian\Model\Types;

use Bristolian\ToArray;

class UserProfile
{
    use ToArray;

    public function __construct(
        public readonly string $user_id,
        public readonly string|null $avatar_image_id,
        public readonly string|null $about_me,
        public readonly \DateTimeInterface $created_at,
        public readonly \DateTimeInterface $updated_at
    ) {
    }
}

