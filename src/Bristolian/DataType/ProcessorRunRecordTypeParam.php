<?php

namespace Bristolian\DataType;

use Bristolian\DataType\PropertyType\BasicPhpEnumTypeOrNull;
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
        public readonly string|null $task_type,
    ) {
    }
}
