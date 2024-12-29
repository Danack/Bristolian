<?php

namespace Bristolian;

class BristolianException extends \Exception
{
    public const CANNOT_INSTANTIATE = "Cannot instantiate object, as it lacks DataType interface.";

    public static function cannot_instantiate(): self
    {
        return new self(self::CANNOT_INSTANTIATE);
    }

    public static function env_variable_is_not_string(string $name, mixed $value): self
    {
        $message = "Getting env variable $name resulted in " . var_export($value, true) . " which is not a string.";
        return new self($message);
    }
}
