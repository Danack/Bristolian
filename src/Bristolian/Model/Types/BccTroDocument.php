<?php

namespace Bristolian\Model\Types;

use Bristolian\ToArray;
use Bristolian\FromArray;
use Bristolian\FromString;

class BccTroDocument
{
    use ToArray;
    use FromString;

    public function __construct(
        public readonly string $title,
        public readonly string $href,
        public readonly string $id
    ) {
    }
}
