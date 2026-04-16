<?php

declare(strict_types = 1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\LinkDescription;
use Bristolian\Parameters\PropertyType\LinkTitle;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Request body for patching a room link's title/description.
 */
class UpdateRoomLinkParam implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[LinkTitle('title')]
        public readonly ?string $title,
        #[LinkDescription('description')]
        public readonly ?string $description,
    ) {
    }
}
