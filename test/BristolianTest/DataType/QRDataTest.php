<?php

namespace BristolianTest\DataType;

use Bristolian\Parameters\PropertyType\BasicDateTime;
use Bristolian\Parameters\PropertyType\BasicString;
use BristolianTest\BaseTestCase;
use Bristolian\Parameters\Params;
use DataType\Create\CreateFromArray;
use Safe\DateTimeImmutable;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\Params
 */
class QRDataTest extends BaseTestCase
{
    public function testWorks()
    {
        $url = "http://www.example.com";

        $params = [
            'url' => "$url",

        ];

        $qr_data = Params::createFromArray($params);

        $this->assertSame($url, $qr_data->url);
    }
}
