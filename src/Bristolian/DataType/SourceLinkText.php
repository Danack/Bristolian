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
