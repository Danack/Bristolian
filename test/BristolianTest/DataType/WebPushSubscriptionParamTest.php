<?php

namespace BristolianTest\DataType;

use BristolianTest\BaseTestCase;
use Bristolian\DataType\WebPushSubscriptionParam;

/**
 * @covers \Bristolian\DataType\WebPushSubscriptionParam
 * @group wip
 */
class WebPushSubscriptionParamTest extends BaseTestCase
{
    public function testWorksWithNull()
    {
        $endpoint = "https://www.example.com/?time=" . microtime();
        $expiration_time = null;

        $example_data = [
            "endpoint" => $endpoint,
            "expirationTime" => $expiration_time,
            "keys" => [
                "p256dh"  => "Some key",
                "auth" => "Some auth key"
            ]
        ];
        $raw = json_encode($example_data);

        $data = [
            "endpoint" => $endpoint,
            "expirationTime" => $expiration_time,
            'raw' => $raw
        ];
        $params = WebPushSubscriptionParam::createFromArray($data);

        $this->assertSame($endpoint, $params->getEndpoint());
        $this->assertSame($expiration_time, $params->getExpirationTime());
        $this->assertSame($raw, $params->getRaw());
    }

    public function testWorksWithTimestampe()
    {
        $endpoint = "https://www.example.com/?time=" . microtime();
        $expiration_time = '12th of whenever. This is a timestamp';

        $example_data = [
            "endpoint" => $endpoint,
            "expirationTime" => $expiration_time,
            "keys" => [
                "p256dh"  => "Some key",
                "auth" => "Some auth key"
            ]
        ];
        $raw = json_encode($example_data);

        $data = [
            "endpoint" => $endpoint,
            "expirationTime" => $expiration_time,
            'raw' => $raw
        ];
        $params = WebPushSubscriptionParam::createFromArray($data);

        $this->assertSame($endpoint, $params->getEndpoint());
        $this->assertSame($expiration_time, $params->getExpirationTime());
        $this->assertSame($raw, $params->getRaw());
    }
}