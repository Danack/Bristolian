<?php

namespace BristolianTest\DataType;

use BristolianTest\BaseTestCase;
use Bristolian\Parameters\WebPushSubscriptionParams;

/**
 * @covers \Bristolian\Parameters\WebPushSubscriptionParams
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
        $params = WebPushSubscriptionParams::createFromArray($data);

        $this->assertSame($endpoint, $params->getEndpoint());
        $this->assertSame($expiration_time, $params->getExpirationTime());
        $this->assertSame($raw, $params->getRaw());
    }

    public function testWorksWithTimestamp()
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
        $params = WebPushSubscriptionParams::createFromArray($data);

        $this->assertSame($endpoint, $params->getEndpoint());
        $this->assertSame($expiration_time, $params->getExpirationTime());
        $this->assertSame($raw, $params->getRaw());
    }
}
