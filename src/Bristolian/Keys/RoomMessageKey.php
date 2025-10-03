<?php

declare(strict_types = 1);

namespace Bristolian\Keys;

class RoomMessageKey
{
    /**
     * @return string
     */
    public static function getAbsoluteKeyName() : string
    {
        return str_replace('\\', '', __CLASS__);
    }
}
