<?php

namespace Bristolian\Exception;

class ContentNotFoundException extends BristolianException
{
    public static function stairs_id_not_found(string $stairs_id): self
    {
        return new self("stairs with id ($stairs_id) not found");
    }


    public static function meme_id_not_found(string $meme_id): self
    {
        return new self("meme with id ($meme_id) not found");
    }

    public static function file_not_found(string $room_id, string $file_id): self
    {
        return new self("file with id ($file_id) in room ($room_id) not found");
    }
}
