<?php

namespace Bristolian\DataType;

use Bristolian\DataType\PropertyType\BasicString;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class DebugParam implements DataType, \Bristolian\StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('message')]
        public readonly string $message,
        #[BasicString('detail')]
        public readonly string $detail,
    ) {
    }
}
