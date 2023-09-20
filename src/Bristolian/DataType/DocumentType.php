<?php

namespace Bristolian\DataType;

use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetString;

#[\Attribute]
class DocumentType implements HasInputType
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
