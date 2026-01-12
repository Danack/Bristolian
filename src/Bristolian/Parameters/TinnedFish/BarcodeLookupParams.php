<?php

declare(strict_types=1);

namespace Bristolian\Parameters\TinnedFish;

use Bristolian\Parameters\PropertyType\OptionalBoolDefaultTrue;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Parameters for barcode lookup API endpoint.
 */
class BarcodeLookupParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[OptionalBoolDefaultTrue('fetch_external')]
        public readonly bool $fetch_external,
    ) {
    }
}
