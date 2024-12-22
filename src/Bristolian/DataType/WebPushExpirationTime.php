<?php

namespace Bristolian\DataType;

use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetStringOrNull;

#[\Attribute]
class WebPushExpirationTime implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    // TODO - this needs tightening to actually check the allowed
    // values
    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetStringOrNull(),
        );
    }
}
