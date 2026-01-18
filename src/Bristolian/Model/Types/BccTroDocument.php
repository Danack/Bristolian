<?php

namespace Bristolian\Model\Types;

use Bristolian\ToArray;

class BccTroDocument
{
    use ToArray;

    public function __construct(
        public readonly string $title,
        public readonly string $href,
        public readonly string $id
    ) {
    }
}
