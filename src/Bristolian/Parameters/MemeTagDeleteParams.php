<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicString;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class MemeTagDeleteParams implements DataType
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('meme_id')]
        public readonly string $meme_id,
        #[BasicString('meme_tag_id')]
        public readonly string $meme_tag_id,
    ) {
    }
}
