<?php

namespace Bristolian\DataType;

use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class LinkParam implements DataType
{
    use CreateFromArray;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[Url('url')]
        public readonly string $url,
        #[LinkTitle('title')]
        public readonly string|null $title,
        #[LinkDescription('description')]
        public readonly string|null $description,
    ) {
    }
}
