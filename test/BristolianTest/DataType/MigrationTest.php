<?php

namespace BristolianTest\DataType;

use Bristolian\DataType\BasicDateTime;
use Bristolian\DataType\BasicString;
use BristolianTest\BaseTestCase;
use Bristolian\DataType\Migration;
use DataType\Create\CreateFromArray;
use Safe\DateTimeImmutable;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\DataType\Migration
 */
class MigrationTest extends BaseTestCase
{
    public function testWorks()
    {
        $id = 123;
        $description = 'This is some description.';
        $checksum = '12345';
        $datetime = new DateTimeImmutable();

        $params = [
            'id' => "$id",
            'description' => $description,
            'checksum' => $checksum,
            'created_at' => $datetime->format("Y-m-d H:i:s"),
        ];

        $migration = Migration::createFromArray($params);

        $this->assertSame($id, $migration->id);
        $this->assertSame($description, $migration->description);
        $this->assertSame($checksum, $migration->checksum);
        $this->assertSame($datetime->format("Y-m-d H:i:s"), $migration->created_at->format("Y-m-d H:i:s"));
    }
}
