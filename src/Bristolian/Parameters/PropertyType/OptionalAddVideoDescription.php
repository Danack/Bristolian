<?php

declare(strict_types=1);

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetStringOrDefault;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\NullIfEmptyString;
use DataType\ProcessRule\RangeStringLength;
use DataType\ProcessRule\SkipIfNull;
use DataType\ProcessRule\TrimOrNull;

/**
 * Optional description when adding a video; same length bounds as {@see ClipDescription}, but the field may be omitted.
 */
#[\Attribute]
class OptionalAddVideoDescription implements HasInputType
{
    public function __construct(
        private string|null $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetStringOrDefault(null),
            new TrimOrNull(),
            new NullIfEmptyString(),
            new SkipIfNull(),
            new RangeStringLength(
                ClipDescription::DESCRIPTION_MINIMUM_LENGTH,
                ClipDescription::DESCRIPTION_MAXIMUM_LENGTH
            )
        );
    }
}
