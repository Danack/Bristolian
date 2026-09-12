<?php

namespace Bristolian\Parameters;

use DataType\Basic\BasicString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class TagParams implements DataType, StaticFactory
{
    use CreateFromRequest;
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
