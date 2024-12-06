<?php

namespace BristolianTest\TestFixtures;

use Bristolian\ToArray;

class ToArrayClassWithDatetime
{
    use ToArray;

    public function __construct(
        public readonly string $foo,
        public readonly \DateTimeInterface $dateTime
    ) {
    }
}
