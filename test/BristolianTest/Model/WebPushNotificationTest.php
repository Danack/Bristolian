<?php

namespace BristolianTest\Model;

use BristolianTest\BaseTestCase;
use Bristolian\Model\WebPushNotification;

/**
 * @coversNothing
 */
class WebPushNotificationTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\WebPushNotification
     */
    public function testCreate()
    {
        $title = 'Test Notification';
        $body = 'This is a test notification body';

        $notification = WebPushNotification::create($title, $body);

        $this->assertSame($title, $notification->getTitle());
        $this->assertSame($body, $notification->getBody());
    }

    /**
     * @covers \Bristolian\Model\WebPushNotification
     */
    public function testGetters()
    {
        $notification = WebPushNotification::create('Title', 'Body');

        $this->assertSame('Title', $notification->getTitle());
        $this->assertSame('Body', $notification->getBody());
        $this->assertSame('/sounds/meow.mp3', $notification->getSound());
    }
}

