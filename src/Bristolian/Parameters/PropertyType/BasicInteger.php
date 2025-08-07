<?php

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetInt;
use DataType\HasInputType;
use DataType\InputType;

#[\Attribute]
class BasicInteger implements HasInputType
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
        );
    }
}
