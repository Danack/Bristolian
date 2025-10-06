<?php

declare(strict_types = 1);

namespace BristolianTest\Parameters;

use Bristolian\Parameters\PropertyType\EmailAddress;
use Bristolian\Parameters\FoiRequestParams;
use Safe\DateTimeImmutable;
use VarMap\ArrayVarMap;
use BristolianTest\BaseTestCase;

use Bristolian\Parameters\PropertyType\PasswordOrRandom;
use Bristolian\Parameters\Params;
use Bristolian\Parameters\Table;

/**
 * @coversNothing
 */
class CoverageTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Parameters\PropertyType\SourceLinkPositionValue
     * @covers \Bristolian\Parameters\PropertyType\BasicString
     * @covers \Bristolian\Parameters\PropertyType\BasicDateTime
     * @covers \Bristolian\Parameters\PropertyType\EmailAddress
     * @covers \Bristolian\Parameters\PropertyType\LinkDescription
     * @covers \Bristolian\Parameters\PropertyType\LinkTitle
     * @covers \Bristolian\Parameters\PropertyType\Url
     * @covers \Bristolian\Parameters\PropertyType\Username
     * @covers \Bristolian\Parameters\PropertyType\WebPushEndPoint
     * @covers \Bristolian\Parameters\PropertyType\WebPushExpirationTime
     * @covers \Bristolian\Parameters\PropertyType\PasswordOrRandom
     *
     *
     *
     */
    public function testWorks()
    {
        $integer = 4;
        $string = 'short text';
        $url = "http://www.example.com";
        $username = "John_the_username";
        $datetime = new DateTimeImmutable();
        $email_address = 'John@example.com';
        $link_title = "some link";
        $link_description = "A description of a link.";
        $web_push_end_point = "https://api.bristolian.org";

        // TODO - This should validate the time?
        $web_push_expiration_time = "Some time";

        $passwordOrRandom = "some_unguessable_password";




        $data = [
            'string' => $string,
            'integer' => $integer,
            'url' => $url,
            'username' => $username,
            'datetime' => $datetime->format("Y-m-d H:i:s"),
            'email_address' => $email_address,
            'link_title' => $link_title,
            'link_description' => $link_description,
            'web_push_end_point' => $web_push_end_point,
            'web_push_expiration_time' => $web_push_expiration_time,
            'password' => $passwordOrRandom
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

        $this->assertSame($link_title, $coverageParam->link_title);
        $this->assertSame($link_description, $coverageParam->link_description);
        $this->assertSame($web_push_end_point, $coverageParam->web_push_end_point);
        $this->assertSame($web_push_expiration_time, $coverageParam->web_push_expiration_time);
        $this->assertSame($passwordOrRandom, $coverageParam->passwordOrRandom);
    }
}
