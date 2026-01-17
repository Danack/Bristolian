<?php

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\BasicPhpEnumTypeOrNull;
use Bristolian\Repo\ProcessorRepo\ProcessType;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

class ProcessorRunRecordTypeParam implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicPhpEnumTypeOrNull('task_type', ProcessType::class)]
        public readonly ProcessType|null $task_type,
    ) {
    }
}


// Business code

// api Parameter types

// PHP internal types and definitions e.g. length
