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

#[\Attribute]
class ClipDescription implements HasInputType
{
    /** Matches room_video.description column. */
    public const DESCRIPTION_MAXIMUM_LENGTH = 12000;

    /** Allow empty description (optional field). */
    public const DESCRIPTION_MINIMUM_LENGTH = 0;

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
            new SkipIfNull(),
            new RangeStringLength(self::DESCRIPTION_MINIMUM_LENGTH, self::DESCRIPTION_MAXIMUM_LENGTH)
        );
    }
}
