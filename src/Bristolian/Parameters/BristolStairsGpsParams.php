<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\GpsFloat;
use Bristolian\StaticFactory;
use DataType\Basic\OptionalLatitudeFloat;
use DataType\Basic\OptionalLongitudeFloat;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * This is used for setting a new flight of stairs position.
 *
 * Safari and other browser strip out GPS info from images. Fun.
 */
class BristolStairsGpsParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[OptionalLatitudeFloat('gps_latitude')]
        public readonly float|null $latitude,
        #[OptionalLongitudeFloat('gps_longitude', 'gps_latitude')]
        public readonly float|null $longitude,
    ) {
    }
}
