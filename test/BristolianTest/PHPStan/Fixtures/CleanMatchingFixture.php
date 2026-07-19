<?php

declare(strict_types=1);

namespace BristolianTest\PHPStan\Fixtures;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\user;
use Bristolian\Database\user_ownership;

#[ReadsTable(user_ownership::class)]
#[WritesTable(user::class)]
#[WritesTable(user_ownership::class)]
class CleanMatchingFixture
{
    public function read(): void
    {
        $sql = user_ownership::SELECT;
    }

    public function write(): void
    {
        $sql = user::INSERT;
        $sql2 = user_ownership::INSERT;
    }
}
