<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\Parameters\PropertyType\TagString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class MemeTagUpdateParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('meme_tag_id')]
        public readonly string $meme_tag_id,
        #[BasicString('type')]
        public readonly string $type,
        #[TagString('text')]
        public readonly string $text,
    ) {
    }
}
