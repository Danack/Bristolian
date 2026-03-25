<?php

declare(strict_types=1);

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetStringOrDefault;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\NullIfEmptyString;
use DataType\ProcessRule\RangeStringLength;
use DataType\ProcessRule\TrimOrNull;

/**
 * Optional title when adding a video to a room; when present, uses the same min length as
 * {@see LinkTitle} and max as {@see ClipTitle} (clips still use stricter {@see ClipTitle} for required titles).
 */
#[\Attribute]
class OptionalAddVideoTitle implements HasInputType
{
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
            new RangeStringLength(LinkTitle::TITLE_MINIMUM_LENGTH, ClipTitle::TITLE_MAXIMUM_LENGTH)
        );
    }
}
