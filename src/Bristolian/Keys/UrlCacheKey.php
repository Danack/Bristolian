<?php

declare(strict_types = 1);

namespace Bristolian\Keys;

class UrlCacheKey
{
    /**
     * @param string $key
     * @return string
     */
    public static function getAbsoluteKeyName(string $key) : string
    {
        // Hash the key to prevent any odd characters from
        // interacting with redis key handling
        $hash = hash("sha256", $key);
        return __CLASS__ . '_' . $hash;
    }
}
