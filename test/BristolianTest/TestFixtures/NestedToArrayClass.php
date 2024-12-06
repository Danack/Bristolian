<?php

namespace BristolianTest\TestFixtures;

use Bristolian\ToArray;

class NestedToArrayClass
{
    use ToArray;

    public function __construct(
        public readonly string $foo,
        public readonly int $bar,
        public readonly ToArrayClass $instance
    ) {
    }
}
