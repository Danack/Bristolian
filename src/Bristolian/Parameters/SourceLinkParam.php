<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\SourceLinkText;
use Bristolian\Parameters\PropertyType\SourceLinkTitle;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class SourceLinkParam implements DataType
{
    use CreateFromArray;
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
