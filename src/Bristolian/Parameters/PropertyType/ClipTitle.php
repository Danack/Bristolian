<?php

declare(strict_types=1);

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\RangeStringLength;

#[\Attribute]
class ClipTitle implements HasInputType
{
    /** Matches room_video.title column. */
    public const TITLE_MAXIMUM_LENGTH = 1024;

    public const TITLE_MINIMUM_LENGTH = 16;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetString(),
            new RangeStringLength(self::TITLE_MINIMUM_LENGTH, self::TITLE_MAXIMUM_LENGTH)
        );
    }
}
