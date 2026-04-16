<?php

declare(strict_types=1);

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetOptionalString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\NullIfEmptyString;
use DataType\ProcessRule\Order;
use DataType\ProcessRule\SkipIfNull;

/**
 * Optional `order` query param for room content lists (files), using DataType ProcessRule Order.
 * Values use +name / -size style; known segments: name, size, added, document_date.
 */
#[\Attribute]
class OptionalRoomContentListOrder implements HasInputType
{
    /** @return list<string> */
    public static function knownOrderNames(): array
    {
        return ['name', 'size', 'added', 'document_date'];
    }

    public function __construct(
        private string $name = 'order'
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetOptionalString(),
            new NullIfEmptyString(),
            new SkipIfNull(),
            new Order(self::knownOrderNames())
        );
    }
}
