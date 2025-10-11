<?php

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\RangeStringLength;

#[\Attribute]
class DisplayName implements HasInputType
{
    const MINIMUM_DISPLAY_NAME_LENGTH = 4;
    const MAXIMUM_DISPLAY_NAME_LENGTH = 32;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetString(),
            new RangeStringLength(self::MINIMUM_DISPLAY_NAME_LENGTH, self::MAXIMUM_DISPLAY_NAME_LENGTH),
        );
    }
}
