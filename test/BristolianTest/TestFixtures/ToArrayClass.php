<?php

namespace BristolianTest\TestFixtures;

use Bristolian\ToArray;

class ToArrayClass
{
    use ToArray;

    public function __construct(
        public readonly string $foo,
        public readonly int $bar
    ) {
    }
}
