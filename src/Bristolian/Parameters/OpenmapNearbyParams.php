<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicFloat;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Query params for "closest OSM stairs to this point" (latitude, longitude from map center).
 */
class OpenmapNearbyParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicFloat('latitude')]
        public readonly float $latitude,
        #[BasicFloat('longitude')]
        public readonly float $longitude,
    ) {
    }
}
