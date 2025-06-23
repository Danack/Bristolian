<?php

namespace Bristolian\DataType\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\IsEmail;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\MinLength;

#[\Attribute]
class EmailAddress implements HasInputType
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
            new MaxLength(256),
            new IsEmail(),
        );
    }
}
