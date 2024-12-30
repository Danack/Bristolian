<?php

namespace Bristolian\DataType;

use DataType\ExtractRule\GetInt;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\RangeIntValue;

#[\Attribute]
class SourceLinkPositionValue implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetInt(),
            // This might be flaky.
            new RangeIntValue(0, 10000),
        );
    }
}
