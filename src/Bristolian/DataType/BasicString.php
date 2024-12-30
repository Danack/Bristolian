<?php

namespace Bristolian\DataType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;

#[\Attribute]
class BasicString implements HasInputType
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
        );
    }
}
