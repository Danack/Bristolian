<?php

declare(strict_types = 1);

namespace Bristolian\Parameters\TinnedFish;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Parameters for generating API token endpoint.
 */
class GenerateApiTokenParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('name')]
        public readonly string $name,
    ) {
    }
}
