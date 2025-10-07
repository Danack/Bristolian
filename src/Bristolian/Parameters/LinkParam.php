<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\LinkDescription;
use Bristolian\Parameters\PropertyType\LinkTitle;
use Bristolian\Parameters\PropertyType\Url;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class LinkParam implements DataType, StaticFactory
{
    use CreateFromRequest;
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
