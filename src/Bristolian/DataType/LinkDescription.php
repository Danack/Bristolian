<?php

namespace Bristolian\DataType;

use Bristolian\DataType\ExtractRule\GetStringOrNull;
use DataType\ExtractRule\GetInt;
use DataType\ExtractRule\GetStringOrDefault;
use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetString;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\NullIfEmptyString;
use DataType\ProcessRule\TrimOrNull;
use DataType\ProcessRule\RangeStringLength;

#[\Attribute]
class LinkDescription implements HasInputType
{
    const DESCRIPTION_MINIMUM_LENGTH = 8;
    const DESCRIPTION_MAXIMUM_LENGTH = 2048;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetStringOrDefault(null),
            new TrimOrNull(),
            new NullIfEmptyString(),
            new RangeStringLength(
                self::DESCRIPTION_MINIMUM_LENGTH,
                self::DESCRIPTION_MAXIMUM_LENGTH
            )
        );
    }
}
