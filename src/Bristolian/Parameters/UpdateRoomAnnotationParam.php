<?php

declare(strict_types=1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\AnnotationText;
use Bristolian\Parameters\PropertyType\AnnotationTitle;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Request body for updating an annotation's title and description text (JSON PATCH).
 */
class UpdateRoomAnnotationParam implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[AnnotationTitle('title')]
        public readonly string $title,
        #[AnnotationText('text')]
        public readonly string $text,
    ) {
    }
}
