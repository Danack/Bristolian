<?php

namespace Bristolian\Model;

use Bristolian\ToArray;

/**
 * Information about some stairs in Bristol as pulled from the database.
 */
class BristolStairInfo
{
    use ToArray;

    public function __construct(
        public readonly string $id,
        public readonly string $latitude,
        public readonly string $longitude,
        public readonly string $description,
        public readonly string $stored_stair_image_file_id,
        public readonly int $steps,
        public readonly int $is_deleted,
        public readonly \DateTimeInterface $created_at,
        public readonly \DateTimeInterface $updated_at
    ) {
    }
}
