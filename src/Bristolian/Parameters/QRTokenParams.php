<?php

declare(strict_types = 1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromVarMap;
use DataType\Create\CreateFromRequest;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class QRTokenParams implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('token')]
        public string $token,
    ) {
    }
}
