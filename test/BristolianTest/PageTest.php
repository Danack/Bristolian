<?php

namespace BristolianTest;

use Bristolian\Page;
use BristolianTest\BaseTestCase;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Page::class, 'getQrShareMessage')]
#[CoversMethod(\Bristolian\Page::class, 'setQrShareMessage')]

class PageTest extends BaseTestCase
{
    private const DEFAULT_QR_MESSAGE = "Show this QR code to someone, and they can scan it with the camera in their device";

    public function teardown(): void
    {
        Page::setQrShareMessage(self::DEFAULT_QR_MESSAGE);
        parent::teardown();
    }

    public function testGetQrShareMessage_returns_default()
    {
        $result = Page::getQrShareMessage();

        $this->assertStringContainsString(self::DEFAULT_QR_MESSAGE, $result);
    }

    public function testSetQrShareMessage_changes_message()
    {
        $custom = "Custom QR share message for testing";

        Page::setQrShareMessage($custom);

        $this->assertSame($custom, Page::getQrShareMessage());
    }

    public function testSetQrShareMessage_with_empty_string()
    {
        Page::setQrShareMessage("");

        $this->assertSame("", Page::getQrShareMessage());
    }
}
