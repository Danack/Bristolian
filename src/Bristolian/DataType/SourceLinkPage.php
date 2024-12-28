<?php

namespace Bristolian\DataType;

use DataType\ExtractRule\GetInt;
use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetString;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\RangeIntValue;

#[\Attribute]
class SourceLinkPage implements HasInputType
{
    const MAX_PAGES = 1000;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetInt(),
            new RangeIntValue(0, self::MAX_PAGES)
        );
    }
}
