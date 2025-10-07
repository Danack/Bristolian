<?php

namespace Bristolian\Exception;

class ContentNotFoundException extends BristolianException
{
    public static function stairs_id_not_found(string $stairs_id): self
    {
        return new self("stairs with id ($stairs_id) not found");
    }
}
