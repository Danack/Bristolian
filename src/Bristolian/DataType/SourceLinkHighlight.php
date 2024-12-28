<?php

namespace Bristolian\DataType;

use DataType\ExtractRule\GetInt;
use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetString;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\RangeStringLength;

#[\Attribute]
class SourceLinkHighlight implements HasInputType
{
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
