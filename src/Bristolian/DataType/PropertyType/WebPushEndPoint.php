<?php

namespace Bristolian\DataType\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\StartsWithString;

#[\Attribute]
class WebPushEndPoint implements HasInputType
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
            new MaxLength(512),
            new StartsWithString('https://')
        );
    }
}
