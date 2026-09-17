<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\FoiRequestParams;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Parameters\FoiRequestParams::class)]

class FoiRequestParamTest extends BaseTestCase
{
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

        $foiRequestParam = FoiRequestParams::createFromVarMap(new ArrayVarMap($data));

        $this->assertSame($text, $foiRequestParam->getText());
        $this->assertSame($url, $foiRequestParam->getUrl());
        $this->assertSame($description, $foiRequestParam->getDescription());
    }
}
