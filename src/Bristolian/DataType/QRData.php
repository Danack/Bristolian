<?php

namespace Bristolian\DataType;

use Bristolian\DataType\PropertyType\Url;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class QRData implements DataType
{
    use CreateFromArray;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[Url('url')]
        public string $url,
    ) {
    }
}
