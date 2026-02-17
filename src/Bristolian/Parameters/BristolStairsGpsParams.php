<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\GpsFloat;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * This is used for setting a new flight of stairs position.
 *
 * Safari and other browser strip out GPS info
 */
class BristolStairsGpsParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[GpsFloat('gps_latitude')]
        public readonly float|null $latitude,
        #[GpsFloat('gps_longitude')]
        public readonly float|null $longitude,
    ) {
    }
}
