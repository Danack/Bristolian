<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\OptionalBasicString;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class MemeSearchParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[OptionalBasicString('query')]
        public readonly ?string $query,
        #[OptionalBasicString('tag_type')]
        public readonly ?string $tag_type,
        #[OptionalBasicString('text_search')]
        public readonly ?string $text_search,
        #[OptionalBasicString('tags')]
        public readonly ?string $tags, // Comma-separated list of exact tag texts to search for
    ) {
    }
}
