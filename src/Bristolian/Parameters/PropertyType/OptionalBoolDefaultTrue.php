<?php

declare(strict_types = 1);

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetOptionalString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\SkipIfNull;

/**
 * Property type for an optional boolean that defaults to true.
 * Accepts "true", "false", "1", "0" as string values.
 */
#[\Attribute]
class OptionalBoolDefaultTrue implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetOptionalString(),
            new \Bristolian\Parameters\ProcessRule\StringToBoolDefaultTrue(),
        );
    }
}
