<?php

declare(strict_types=1);

namespace Bristolian\Parameters;

use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

use Bristolian\Parameters\PropertyType\ClipTitle;
use Bristolian\Parameters\PropertyType\ClipDescription;

/**
 * Request body for updating a room video's title and/or description.
 */
class UpdateRoomVideoParam implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[ClipTitle('title')]
        public readonly ?string $title,
        #[ClipDescription('description')]
        public readonly ?string $description,
    ) {
    }
}
