<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan\Fixtures;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Database\user;

/**
 * Declares ReadsTable but only writes.
 */
#[ReadsTable(user::class)]
class WrongModeFixture
{
    public function update(): void
    {
        $sql = user::UPDATE;
    }
}
