<?php

declare(strict_types = 1);

namespace Bristolian;

trait FromString
{
    public static function fromArray(array $data): self
    {
        $reflection = new \ReflectionClass(static::class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            // No constructor — create a plain instance
            return $reflection->newInstance();
        }

        $params = [];
        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            $value = null;

            if (array_key_exists($name, $data)) {
                $value = $data[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $value = $param->getDefaultValue();
            } else {
                throw new \InvalidArgumentException("Missing required key '$name' in data for " . static::class);
            }

            // Detect DateTime-like parameters
            // TODO - why is $type unused?
            // TODO - Why is PhpStan not detecting $type as unused.
            $type = $param->getType();
            $isDateField = in_array($name, ['created_at', 'updated_at'], true);
            if ($isDateField && is_string($value)) {
                try {
                    $value = new \DateTimeImmutable($value);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException(
                        "Invalid datetime format for '$name': " . $e->getMessage()
                    );
                }
            }

            $params[] = $value;
        }

        return $reflection->newInstanceArgs($params);
    }

    public static function fromString(string $string):self
    {
        return self::fromArray(json_decode_safe($string));
    }
}
