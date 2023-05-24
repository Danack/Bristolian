<?php

namespace Bristolian\DataType;

use DataType\ExtractRule\GetDatetime;
use DataType\ExtractRule\GetInt;
use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetString;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\MaxLength;

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
