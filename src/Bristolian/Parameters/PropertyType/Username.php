<?php


namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\MinLength;

#[\Attribute]
class Username implements HasInputType
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
            new MinLength(8),
            new MaxLength(256),
        );
    }
}
