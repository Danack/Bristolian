<?php

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetFloat;
use DataType\ExtractRule\GetOptionalFloat;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\SkipIfNull;

#[\Attribute]
class GpsFloat implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetOptionalFloat(),
            new SkipIfNull(),
            // TODO - add sanity checks?
        );
    }
}
