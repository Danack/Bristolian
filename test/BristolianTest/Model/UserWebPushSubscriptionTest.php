<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\UserWebPushSubscription;

/**
 * @coversNothing
 */
class UserWebPushSubscriptionTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\UserWebPushSubscription
     */
    public function testConstruct()
    {
        $endpoint = 'https://push.example.com/endpoint';
        $expirationTime = '2025-12-31 23:59:59';
        $raw = '{"endpoint": "https://push.example.com/endpoint"}';

        $subscription = new UserWebPushSubscription($endpoint, $expirationTime, $raw);

        $this->assertSame($endpoint, $subscription->getEndpoint());
        $this->assertSame($expirationTime, $subscription->getExpirationTime());
        $this->assertSame($raw, $subscription->getRaw());
    }

    /**
     * @covers \Bristolian\Model\UserWebPushSubscription
     */
    public function testGetters()
    {
        $subscription = new UserWebPushSubscription(
            'https://endpoint.com',
            '2025-01-01',
            '{"data": "test"}'
        );

        $this->assertSame('https://endpoint.com', $subscription->getEndpoint());
        $this->assertSame('2025-01-01', $subscription->getExpirationTime());
        $this->assertSame('{"data": "test"}', $subscription->getRaw());
    }
}

