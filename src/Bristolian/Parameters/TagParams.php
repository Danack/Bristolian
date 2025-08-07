<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class TagParams implements DataType
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
