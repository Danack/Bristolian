<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan\Fixtures;

use Bristolian\Database\user;

/**
 * Missing WritesTable for INSERT usage — used only as a parse fixture.
 */
class MissingWritesTableFixture
{
    public function insert(): void
    {
        $sql = user::INSERT;
    }
}
