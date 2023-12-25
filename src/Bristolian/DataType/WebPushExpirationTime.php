<?php

namespace Bristolian\DataType;

use DataType\InputType;
use DataType\HasInputType;
use Bristolian\DataType\ExtractRule\GetStringOrNull;

#[\Attribute]
class WebPushExpirationTime implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetStringOrNull(),
        );
    }
}
