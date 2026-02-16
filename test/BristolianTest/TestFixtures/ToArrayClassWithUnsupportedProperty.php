<?php

namespace BristolianTest\TestFixtures;

use Bristolian\ToArray;

class ToArrayClassWithUnsupportedProperty
{
    use ToArray;

    public function __construct(
        public readonly string $foo,
        public readonly \stdClass $unsupported
    ) {
    }
}
