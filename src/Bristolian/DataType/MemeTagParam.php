<?php

namespace Bristolian\DataType;

use Bristolian\DataType\PropertyType\BasicString;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class MemeTagParam implements DataType
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('meme_id')]
        public readonly string $meme_id,
        #[BasicString('type')]
        public readonly string $type,
        #[BasicString('text')]
        public readonly string $text,
    ) {
    }
}
