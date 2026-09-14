<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan\Fixtures;

use Bristolian\Attribute\SkipTableAttributes;
use BristolianGenerated\Database\user;

/**
 * Would mismatch without SkipTableAttributes.
 */
#[SkipTableAttributes('intentional opt-out for test')]
class SkipTableAttributesFixture
{
    public function insert(): void
    {
        $sql = user::INSERT;
    }
}
