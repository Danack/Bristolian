<?php

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetStringOrDefault;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\NullIfEmptyString;
use DataType\ProcessRule\RangeStringLength;
use DataType\ProcessRule\TrimOrNull;

#[\Attribute]
class LinkTitle implements HasInputType
{
    const TITLE_MINIMUM_LENGTH = 8;
    const TITLE_MAXIMUM_LENGTH = 2048;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetStringOrDefault(null),
            new TrimOrNull(),
            new NullIfEmptyString(),
            new RangeStringLength(self::TITLE_MINIMUM_LENGTH, self::TITLE_MAXIMUM_LENGTH)
        );
    }
}
