<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\TagParams;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Parameters\TagParams::class)]

class TagParamTest extends BaseTestCase
{
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
