<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan\Fixtures\OutsideEnforced;

use BristolianGenerated\Database\user;

/**
 * Fixture for RepoTableAttributesRule path filtering.
 *
 * That rule only checks classes whose file path is under the configured
 * enforcedDirectories (by default src/Bristolian/Repo in phpstan.neon).
 * This class sits under test/.../OutsideEnforced/, so even though it uses
 * a Database::* constant without #[WritesTable], the rule must ignore it.
 */
class OutsideEnforcedDirectoryFixture
{
    public function insert(): void
    {
        $sql = user::INSERT;
    }
}
