<?php

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\ValidUrl;

#[\Attribute]
class Url implements HasInputType
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
            new MinLength(1),
            new MaxLength(2048), // TODO - needs better validation
            new ValidUrl(false)
        );
    }
}
