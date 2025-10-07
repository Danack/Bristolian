<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicInteger;
use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class BristolStairsInfoParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('bristol_stair_info_id')]
        public readonly string $bristol_stair_info_id,
        #[BasicString('description')]
        public readonly string $description,
        #[BasicInteger('steps')]
        public readonly string $steps,
    ) {
    }
}
