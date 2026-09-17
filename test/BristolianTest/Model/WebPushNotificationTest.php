<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\WebPushNotification;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(\Bristolian\Model\Types\WebPushNotification::class)]

class WebPushNotificationTest extends BaseTestCase
{
    public function testCreate(): void
    {
        $title = 'Test Notification';
        $body = 'This is a test notification body';

        $notification = WebPushNotification::create($title, $body);

        $this->assertSame($title, $notification->getTitle());
        $this->assertSame($body, $notification->getBody());
    }

    public function testGetters(): void
    {
        $notification = WebPushNotification::create('Title', 'Body');

        $this->assertSame('Title', $notification->getTitle());
        $this->assertSame('Body', $notification->getBody());
        $this->assertSame('/sounds/meow.mp3', $notification->getSound());
        // getVibrate() and getData() return null by default (tested via type/API)
    }
}
