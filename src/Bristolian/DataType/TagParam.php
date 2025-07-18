<?php

namespace Bristolian\DataType;

use Bristolian\DataType\PropertyType\BasicString;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class TagParam implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('text')]
        public readonly string $text,
        #[BasicString('description')]
        public readonly string $description,
    ) {
    }
}
