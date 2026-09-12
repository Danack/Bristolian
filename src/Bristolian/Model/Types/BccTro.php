<?php

namespace Bristolian\Model\Types;

use Bristolian\FromString;
use Bristolian\StaticFactory;
use Bristolian\ToString;
use Bristolian\ToArray;
use DataType\Basic\BasicString;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromJson;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\ExtractRule\GetType;
use DataType\ExtractRule\GetTypeOrNull;
use DataType\InputType\GetDataType;

class BccTro implements DataType //, StaticFactory
{
    use ToString;

    use CreateFromJson;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('title')]
        public readonly string $title,
        #[BasicString('reference_code')]
        public readonly string $reference_code,
        #[GetDataType('statement_of_reasons', BccTroDocument::class)]
        public readonly BccTroDocument $statement_of_reasons,
        #[GetDataType('notice_of_proposal', BccTroDocument::class)]
        public readonly BccTroDocument $notice_of_proposal,
        #[GetDataType('proposed_plan', BccTroDocument::class)]
        public readonly BccTroDocument $proposed_plan
    ) {
    }
}
