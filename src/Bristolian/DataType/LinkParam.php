<?php

namespace Bristolian\DataType;

use Bristolian\DataType\PropertyType\LinkDescription;
use Bristolian\DataType\PropertyType\LinkTitle;
use Bristolian\DataType\PropertyType\Url;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class LinkParam implements DataType
{
    use CreateFromArray;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[Url('url')]
        public readonly string $url,
        #[LinkTitle('title')]
        public readonly string|null $title,
        #[LinkDescription('description')]
        public readonly string|null $description,
    ) {
    }
}
