<?php

declare(strict_types = 1);

namespace Bristolian\Key;

class UrlCacheKey
{
    /**
     * @param string $key
     * @return string
     */
    public static function getAbsoluteKeyName(string $uri) : string
    {
        // Hash the key to prevent any odd characters from
        // interacting with redis key handling
        $key = hash("sha256", $uri);
        return __CLASS__ . '_' . $key;
    }
}
