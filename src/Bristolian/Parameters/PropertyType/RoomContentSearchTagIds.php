<?php

declare(strict_types=1);

namespace Bristolian\Parameters\PropertyType;

use Bristolian\Parameters\ProcessRule\OptionalStringToRoomSearchTagIds;
use DataType\ExtractRule\GetOptionalString;
use DataType\HasInputType;
use DataType\InputType;

/**
 * Optional comma-separated tag ids for room content search.
 * Parses to string[], max 5 tags, trims each, rejects empty tags.
 */
#[\Attribute]
class RoomContentSearchTagIds implements HasInputType
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
            new OptionalStringToRoomSearchTagIds(),
        );
    }
}
