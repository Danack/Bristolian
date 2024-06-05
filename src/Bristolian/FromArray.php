<?php

declare(strict_types = 1);

namespace Bristolian;

trait FromArray
{
    /**
     * @param array $data
     * @return static
     * @throws \ReflectionException
     */
    public static function fromArray(array $data): self
    {
        $reflection = new \ReflectionClass(__CLASS__);
        $instance = $reflection->newInstanceWithoutConstructor();

        foreach ($instance as $key => &$property) {
            echo "setting key $key \n";
            $property = $data[$key];
        }

        return $instance;
    }
}
