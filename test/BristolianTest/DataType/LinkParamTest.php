<?php

declare(strict_types = 1);

namespace BristolianTest\DataType;

use Bristolian\DataType\LinkParam;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class LinkParamTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\DataType\LinkParam
     */
    public function testWorks()
    {
        $unique = date("Ymdhis").uniqid();

        $title = 'short text ' . $unique;
        $description = 'this is a description ' . $unique;
        $url = "http://www.example.com?unique=" . $unique;

        $data = [
            'title' => $title,
            'description' => $description,
            'url' => $url,
        ];

        $linkParam = LinkParam::createFromVarMap(new ArrayVarMap($data));

        $this->assertSame($title, $linkParam->title);
        $this->assertSame($url, $linkParam->url);
        $this->assertSame($description, $linkParam->description);
    }
}
