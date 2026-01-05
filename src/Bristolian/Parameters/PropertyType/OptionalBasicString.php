<?php

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetOptionalString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\SkipIfNull;

#[\Attribute]
class OptionalBasicString implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetOptionalString(),
            new SkipIfNull(),
        );
    }
}

