<?php

declare(strict_types=1);

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetStringOrNull;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\NullIfEmptyString;
use DataType\ProcessRule\RangeStringLength;
use DataType\ProcessRule\SkipIfNull;
use DataType\ProcessRule\TrimOrNull;

#[\Attribute]
class ClipTitle implements HasInputType
{
    /** Matches room_video.title column. */
    public const TITLE_MAXIMUM_LENGTH = 1024;

    public const TITLE_MINIMUM_LENGTH = 1;

    public function __construct(
        private string|null $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetStringOrNull(),
            new TrimOrNull(),
            new NullIfEmptyString(),
            new SkipIfNull(),
            new RangeStringLength(self::TITLE_MINIMUM_LENGTH, self::TITLE_MAXIMUM_LENGTH)
        );
    }
}
