<?php

declare(strict_types = 1);

namespace Bristolian\Key;

class PhpBugsMaxCommentStorageKey
{
    /**
     * @param string $externalSource
     * @return string
     */
    public static function getAbsoluteKeyName() : string
    {
        return __CLASS__ ;
    }
}
