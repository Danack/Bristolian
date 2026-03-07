<?php

declare(strict_types=1);

namespace Bristolian\Keys;

class UnknownCacheQueryKey
{
    public const SET_KEY = 'UnknownCacheQueryKey_set';

    public static function getAbsoluteKeyName(string $key): string
    {
        $hash = hash("sha256", $key);
        return __CLASS__ . '_' . $hash;
    }
}
