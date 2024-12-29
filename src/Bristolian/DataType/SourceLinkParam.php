<?php

namespace Bristolian\DataType;

use DataType\Create\CreateFromRequest;
use DataType\DataType;
use DataType\Create\CreateFromVarMap;
use DataType\GetInputTypesFromAttributes;

class SourceLinkParam implements DataType
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    /**
     * @param string $title
     * @param string $highlights_json
     * @param string $text
     */
    public function __construct(
        #[SourceLinkTitle('title')]
        public readonly string $title,
        #[SourceLinkHighlightsJson('highlights_json')]
        public readonly string $highlights_json,
        #[SourceLinkText('text')]
        public readonly string $text,
    ) {
    }
}
