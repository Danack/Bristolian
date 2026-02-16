<?php

namespace BristolianTest\TestFixtures;

use Bristolian\ToArray;

class ToArrayClassWithSkippedProperty
{
    use ToArray;

    public function __construct(
        public readonly string $foo,
        public readonly string $__internal = 'should-be-skipped'
    ) {
    }
}
