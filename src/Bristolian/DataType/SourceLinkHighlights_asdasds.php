<?php

namespace Bristolian\DataType;

use DataType\Create\CreateFromArray;
use DataType\DataType;
use DataType\ExtractRule\GetInt;
use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetString;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\RangeStringLength;
use DataType\ExtractRule\GetArrayOfType;

#[\Attribute]
class SourceLinkHighlightsAsdasds implements DataType
{
    use CreateFromArray;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetArrayOfType(SourceLinkHighlightParam::class),
        );
    }
}
