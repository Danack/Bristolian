<?php

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetIntOrNull;
use DataType\ExtractRule\GetOptionalInt;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\SkipIfNull;

#[\Attribute]
class ChatMessageReplyId implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetOptionalInt(),
            new SkipIfNull(),
            // todo - more sanity checks?
        );
    }
}
