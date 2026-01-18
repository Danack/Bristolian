<?php

declare(strict_types = 1);

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\CheckOnlyAllowedCharacters;

#[\Attribute]
class TagString implements HasInputType
{
    const MINIMUM_TAG_LENGTH = 4;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetString(),
            new MinLength(self::MINIMUM_TAG_LENGTH),
            new CheckOnlyAllowedCharacters('a-zA-Z0-9\-_\'"')
        );
    }
}
