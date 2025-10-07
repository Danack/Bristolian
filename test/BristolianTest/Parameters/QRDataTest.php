<?php

namespace BristolianTest\Parameters;

use Bristolian\Parameters\PropertyType\BasicDateTime;
use Bristolian\Parameters\PropertyType\BasicString;
use BristolianTest\BaseTestCase;
use Bristolian\Parameters\QRParams;
use DataType\Create\CreateFromArray;
use Safe\DateTimeImmutable;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\Parameters\QRParams
 */
class QRDataTest extends BaseTestCase
{
    public function testWorks()
    {
        $url = "http://www.example.com";

        $params = [
            'url' => "$url",

        ];

        $qr_data = QRParams::createFromArray($params);

        $this->assertSame($url, $qr_data->url);
    }
}
