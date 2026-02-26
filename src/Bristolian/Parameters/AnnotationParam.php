<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\AnnotationHighlightsJson;
use Bristolian\Parameters\PropertyType\AnnotationText;
use Bristolian\Parameters\PropertyType\AnnotationTitle;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class AnnotationParam implements DataType, StaticFactory
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
        #[AnnotationTitle('title')]
        public readonly string $title,
        #[AnnotationHighlightsJson('highlights_json')]
        public readonly string $highlights_json,
        #[AnnotationText('text')]
        public readonly string $text,
    ) {
    }
}
