<?php

declare(strict_types = 1);

namespace BristolianTest\DataType;

use Bristolian\DataType\FoiRequestParam;
use Safe\DateTimeImmutable;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class CoverageTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\DataType\BasicInteger
     * @covers \Bristolian\DataType\BasicString
     * @covers \Bristolian\DataType\Url
     * @covers \Bristolian\DataType\Username
     */
    public function testWorks()
    {
        $integer = 4;
        $string = 'short text';
        $url = "http://www.example.com";
        $username = "John";
        $datetime = new DateTimeImmutable();
        $email_address = 'John@example.com';

        $data = [
            'string' => $string,
            'integer' => $integer,
            'url' => $url,
            'username' => $username,
            'datetime' => $datetime->format("Y-m-d H:i:s"),
            'email_address' => $email_address
        ];

        $coverageParam = CoverageParam::createFromVarMap(new ArrayVarMap($data));

        $this->assertSame($string, $coverageParam->getString());
        $this->assertSame($integer, $coverageParam->getInteger());
        $this->assertSame($url, $coverageParam->getUrl());
        $this->assertSame($username, $coverageParam->getUsername());
        $this->assertSame(
            $datetime->format("Y-m-d H:i:s"),
            $coverageParam->getDatetime()->format("Y-m-d H:i:s"),
        );

    }
}
