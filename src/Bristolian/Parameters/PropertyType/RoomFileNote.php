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

/**
 * Longer explanation text for a room file ({@see room_file}.note, varchar 12000).
 */
#[\Attribute]
class RoomFileNote implements HasInputType
{
    public const MAXIMUM_LENGTH = 12000;

    public const MINIMUM_LENGTH = 0;

    public function __construct(
        private string $name
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
            new RangeStringLength(self::MINIMUM_LENGTH, self::MAXIMUM_LENGTH)
        );
    }
}
