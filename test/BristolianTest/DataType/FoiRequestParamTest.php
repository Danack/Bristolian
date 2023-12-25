<?php

declare(strict_types = 1);

namespace BristolianTest\DataType;

use Bristolian\DataType\FoiRequestParam;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FoiRequestParamTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\DataType\FoiRequestParam
     */
    public function testWorks()
    {
        $unique = date("Ymdhis").uniqid();

        $text = 'short text ' . $unique;
        $description = 'this is a description ' . $unique;
        $url = "http://www.example.com?unique=" . $unique;

        $data = [
            'text' => $text,
            'description' => $description,
            'url' => $url,
        ];

        $foiRequestParam = FoiRequestParam::createFromVarMap(new ArrayVarMap($data));

        $this->assertSame($text, $foiRequestParam->getText());
        $this->assertSame($url, $foiRequestParam->getUrl());
        $this->assertSame($description, $foiRequestParam->getDescription());
    }
}
