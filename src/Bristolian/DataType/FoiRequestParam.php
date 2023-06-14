<?php

namespace Bristolian\DataType;

use DataType\DataType;
use DataType\Create\CreateFromVarMap;
use DataType\GetInputTypesFromAttributes;

class FoiRequestParam implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('text')]
        public string $text,
        #[Url('text')]
        public string $url,
        #[BasicString('description')]
        public string $description,
    ) {
    }
}
