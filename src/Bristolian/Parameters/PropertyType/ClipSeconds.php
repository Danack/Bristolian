<?php

declare(strict_types=1);

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetInt;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\RangeIntValue;

#[\Attribute]
class ClipSeconds implements HasInputType
{
    /** Maximum clip duration: 10 hours in seconds. */
    public const MAX_SECONDS = 36000;

    public const MIN_SECONDS = 0;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetInt(),
            new RangeIntValue(self::MIN_SECONDS, self::MAX_SECONDS)
        );
    }
}
