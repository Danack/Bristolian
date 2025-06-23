<?php

namespace Bristolian\DataType;

use Bristolian\DataType\PropertyType\SourceLinkPage;
use Bristolian\DataType\PropertyType\SourceLinkPositionValue;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class SourceLinkHighlightParam implements DataType
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[SourceLinkPage('page')]
        public readonly int $page,
        #[SourceLinkPositionValue('left')]
        public readonly int $left,
        #[SourceLinkPositionValue('top')]
        public readonly int $top,
        #[SourceLinkPositionValue('right')]
        public readonly int $right,
        #[SourceLinkPositionValue('bottom')]
        public readonly int $bottom,
    ) {
    }
}
