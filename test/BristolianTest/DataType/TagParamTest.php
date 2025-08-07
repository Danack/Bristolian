<?php

declare(strict_types = 1);

namespace BristolianTest\DataType;

use Bristolian\Parameters\TagParams;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class TagParamTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Parameters\TagParams
     */
    public function testWorks()
    {
        $unique = date("Ymdhis").uniqid();

        $text = 'short text ' . $unique;
        $description = 'this is a description ' . $unique;
        $data = [
            'text' => $text,
            'description' => $description,
        ];

        $TagParam = TagParams::createFromVarMap(new ArrayVarMap($data));

        $this->assertSame($text, $TagParam->text);
        $this->assertSame($description, $TagParam->description);
    }
}
