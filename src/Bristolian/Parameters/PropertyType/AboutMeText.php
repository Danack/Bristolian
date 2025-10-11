<?php

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\RangeStringLength;

#[\Attribute]
class AboutMeText implements HasInputType
{
    const MINIMUM_ABOUT_ME_LENGTH = 0;
    const MAXIMUM_ABOUT_ME_LENGTH = 4096;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetString(),
            new RangeStringLength(
                self::MINIMUM_ABOUT_ME_LENGTH,
                self::MAXIMUM_ABOUT_ME_LENGTH
            ),
        );
    }
}
