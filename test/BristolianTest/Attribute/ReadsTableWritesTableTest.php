<?php

declare(strict_types=1);

namespace BristolianTest\Attribute;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use BristolianGenerated\Database\user;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReadsTable::class)]
#[CoversClass(WritesTable::class)]
class ReadsTableWritesTableTest extends BaseTestCase
{
    public function test_readsTable_exposes_table_helper_class(): void
    {
        $attribute = new ReadsTable(user::class);
        $this->assertSame(user::class, $attribute->tableHelperClass);
    }

    public function test_writesTable_exposes_table_helper_class(): void
    {
        $attribute = new WritesTable(user::class);
        $this->assertSame(user::class, $attribute->tableHelperClass);
    }
}
