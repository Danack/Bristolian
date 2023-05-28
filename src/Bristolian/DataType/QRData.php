<?php

namespace Bristolian\DataType;

use DataType\DataType;
use DataType\Create\CreateFromVarMap;
use DataType\GetInputTypesFromAttributes;

class QRData implements DataType
{

    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[Url('url')]
        public string $url,
    ) {
    }
}
