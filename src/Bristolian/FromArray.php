<?php

declare(strict_types = 1);

namespace Bristolian;

trait FromArray
{
    /**
     * @param array<mixed> $data
     * @return static
     * @throws \ReflectionException
     */
    public static function fromArray(array $data): static
    {
        $reflection = new \ReflectionClass(__CLASS__);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($instance as $key => &$property) {
            $property = $data[$key];
        }

        /* @phpstan-ignore return.type */
        return $instance;
    }
}
