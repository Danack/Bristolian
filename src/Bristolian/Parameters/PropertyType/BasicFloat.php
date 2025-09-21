<?php

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetFloat;
use DataType\HasInputType;
use DataType\InputType;

#[\Attribute]
class BasicFloat implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetFloat(),
        );
    }
}
