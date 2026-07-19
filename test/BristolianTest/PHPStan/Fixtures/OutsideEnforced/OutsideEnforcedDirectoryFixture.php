<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan\Fixtures\OutsideEnforced;

use Bristolian\Database\user;

/**
 * Lives outside src/Bristolian/Repo — should not be enforced by default config.
 */
class OutsideEnforcedDirectoryFixture
{
    public function insert(): void
    {
        $sql = user::INSERT;
    }
}
