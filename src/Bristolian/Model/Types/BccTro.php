<?php

namespace Bristolian\Model\Types;

use Bristolian\FromString;
use Bristolian\StaticFactory;
use Bristolian\ToString;
use Bristolian\ToArray;
use Bristolian\Parameters\PropertyType\BasicString;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;
use DataType\ExtractRule\GetType;
use DataType\ExtractRule\GetTypeOrNull;

class BccTro //implements DataType, StaticFactory
{
    use ToString;
//    use FromString;

    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
//        #[BasicString('title')]
        public readonly string $title,
//        #[BasicString('reference_code')]
        public readonly string $reference_code,

        public readonly BccTroDocument $statement_of_reasons,
        public readonly BccTroDocument $notice_of_proposal,
        public readonly BccTroDocument $proposed_plan
    ) {
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode_safe($json);

        $title = $data['title'];
        $reference_code = $data['reference_code'];

        $statement_of_reasons = new BccTroDocument(
            $data['statement_of_reasons']['title'],
            $data['statement_of_reasons']['href'],
            $data['statement_of_reasons']['id'],
        );

        $notice_of_proposal = new BccTroDocument(
            $data['notice_of_proposal']['title'],
            $data['notice_of_proposal']['href'],
            $data['notice_of_proposal']['id'],
        );

        $proposed_plan = new BccTroDocument(
            $data['proposed_plan']['title'],
            $data['proposed_plan']['href'],
            $data['proposed_plan']['id'],
        );

        return new self(
            $title,
            $reference_code,
            $statement_of_reasons,
            $notice_of_proposal,
            $proposed_plan
        );
    }
}
