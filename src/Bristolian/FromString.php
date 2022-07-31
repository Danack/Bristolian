<?php

declare(strict_types = 1);

namespace Bristolian;

trait FromString
{
    public static function fromArray(array $data): self
    {
        $reflection = new \ReflectionClass(__CLASS__);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($instance as $key => &$property) {
            $property = $data[$key];
        }

        return $instance;
    }

    public static function fromString(string $string):self
    {
        return self::fromArray(json_decode_safe($string));
    }
}
