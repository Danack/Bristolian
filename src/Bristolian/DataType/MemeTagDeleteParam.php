<?php

namespace Bristolian\DataType;

use DataType\Create\CreateFromRequest;
use DataType\DataType;
use DataType\Create\CreateFromVarMap;
use DataType\GetInputTypesFromAttributes;

class MemeTagDeleteParam implements DataType
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
