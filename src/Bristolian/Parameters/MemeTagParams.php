<?php

namespace Bristolian\Parameters;

use DataType\Basic\BasicString;
use Bristolian\Parameters\PropertyType\TagString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class MemeTagParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('meme_id')]
        public readonly string $meme_id,
        #[BasicString('type')]
        public readonly string $type,
        #[TagString('text')]
        public readonly string $text,
    ) {
    }
}
