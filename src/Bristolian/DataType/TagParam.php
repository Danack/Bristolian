<?php

namespace Bristolian\DataType;

use DataType\DataType;
use DataType\Create\CreateFromVarMap;
use DataType\GetInputTypesFromAttributes;

class TagParam implements DataType
{

    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('text')]
        public string $text,
        #[BasicString('description')]
        public string $description,
    ) {
    }
}
