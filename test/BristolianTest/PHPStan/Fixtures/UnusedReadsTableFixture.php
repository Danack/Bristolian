<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan\Fixtures;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Database\user_ownership;

/**
 * Declares ReadsTable but never uses SELECT.
 */
#[ReadsTable(user_ownership::class)]
class UnusedReadsTableFixture
{
}
