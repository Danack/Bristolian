<?php

namespace Bristolian\Model\Types;

use Bristolian\FromString;
use Bristolian\ToString;
use Bristolian\ToArray;

class BccTro
{
    use ToString;
    use FromString;

    public function __construct(
        public readonly string $title,
        public readonly string $reference_code,
        public readonly BccTroDocument $statement_of_reasons,
        public readonly BccTroDocument $notice_of_proposal,
        public readonly BccTroDocument $proposed_plan
    ) {
    }
}
