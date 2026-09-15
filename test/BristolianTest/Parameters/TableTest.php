<?php

namespace BristolianTest\Parameters;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\Table;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Parameters\Table::class)]
class TableTest extends BaseTestCase
{
    public function testWorks()
    {
        $table_rows = 123;
        $table_name = 'foo';

        $params = [
            'TABLE_ROWS' => $table_rows,
            'TABLE_NAME' => $table_name
        ];

        $table = Table::createFromArray($params);

        $this->assertSame($table_name, $table->name);
        $this->assertSame($table_rows, $table->number_of_rows);
    }
}
