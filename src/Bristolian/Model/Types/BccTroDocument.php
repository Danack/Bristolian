<?php

namespace Bristolian\Model\Types;

use Bristolian\StaticFactory;
use Bristolian\ToArray;
use Bristolian\FromArray;
use Bristolian\FromString;
use Bristolian\Parameters\PropertyType\BasicString;
use Bristolian\ToString;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class BccTroDocument implements DataType, StaticFactory
{
    use ToArray;
    use FromString;

    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('title')]
        public readonly string $title,
        #[BasicString('href')]
        public readonly string $href,
        #[BasicString('id')]
        public readonly string $id
    ) {
    }
}
