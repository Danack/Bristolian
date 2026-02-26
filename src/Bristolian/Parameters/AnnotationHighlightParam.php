<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\AnnotationPage;
use Bristolian\Parameters\PropertyType\AnnotationPositionValue;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class AnnotationHighlightParam implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[AnnotationPage('page')]
        public readonly int $page,
        #[AnnotationPositionValue('left')]
        public readonly int $left,
        #[AnnotationPositionValue('top')]
        public readonly int $top,
        #[AnnotationPositionValue('right')]
        public readonly int $right,
        #[AnnotationPositionValue('bottom')]
        public readonly int $bottom,
    ) {
    }
}
