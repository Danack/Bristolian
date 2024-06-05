<?php

namespace Bristolian;

class BristolianException extends \Exception
{
    public const CANNOT_INSTANTIATE = "Cannot instantiate object, as it lacks DataType interface.";

    public static function cannot_instantiate()
    {
        return new self(self::CANNOT_INSTANTIATE);
    }
}