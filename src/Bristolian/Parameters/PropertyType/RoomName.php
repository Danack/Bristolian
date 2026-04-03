<?php

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\RangeStringLength;

#[\Attribute]
class RoomName implements HasInputType
{
    public const MINIMUM_LENGTH = 1;

    public const MAXIMUM_LENGTH = 36;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetString(),
            new RangeStringLength(self::MINIMUM_LENGTH, self::MAXIMUM_LENGTH)
        );
    }
}
