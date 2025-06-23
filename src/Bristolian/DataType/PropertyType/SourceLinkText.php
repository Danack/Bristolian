<?php

namespace Bristolian\DataType\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\RangeStringLength;

#[\Attribute]
class SourceLinkText implements HasInputType
{
    public const MAXIMUM_LENGTH = 16 * 1024;

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
                0,  // highlight could be a diagram?
                self::MAXIMUM_LENGTH
            )
        );
    }
}
