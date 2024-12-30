<?php

namespace Bristolian\DataType;

use DataType\ExtractRule\GetDatetime;
use DataType\HasInputType;
use DataType\InputType;

#[\Attribute]
class BasicDateTime implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetDatetime(["Y-m-d H:i:s"]),
        );
    }
}
