<?php

declare(strict_types=1);

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\RangeStringLength;
use DataType\ProcessRule\TrimOrNull;

#[\Attribute]
class RoomNoteTitle implements HasInputType
{
    public const MINIMUM_LENGTH = 1;

    public const MAXIMUM_LENGTH = 1024;

    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetString(),
            new TrimOrNull(),
            new RangeStringLength(self::MINIMUM_LENGTH, self::MAXIMUM_LENGTH)
        );
    }
}
