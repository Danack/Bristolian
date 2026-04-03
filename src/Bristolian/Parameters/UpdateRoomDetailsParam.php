<?php

declare(strict_types = 1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\AboutMeText;
use Bristolian\Parameters\PropertyType\RoomName;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Request body for PATCH /api/rooms/{room_id}/details (name + purpose / description text).
 */
class UpdateRoomDetailsParam implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[RoomName('name')]
        public readonly string $name,
        #[AboutMeText('purpose')]
        public readonly string $purpose,
    ) {
    }
}
