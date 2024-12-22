<?php

namespace BristolianTest\DataType;

use Bristolian\DataType\BasicDateTime;
use Bristolian\DataType\BasicString;
use BristolianTest\BaseTestCase;
use Bristolian\DataType\QRData;
use DataType\Create\CreateFromArray;
use Safe\DateTimeImmutable;
use VarMap\ArrayVarMap;

/**
 * @covers \Bristolian\DataType\QRData
 */
class QRDataTest extends BaseTestCase
{
    public function testWorks()
    {
        $url = "http://www.example.com";

        $params = [
            'url' => "$url",

        ];

        $qr_data = QRData::createFromArray($params);

        $this->assertSame($url, $qr_data->url);
    }
}
