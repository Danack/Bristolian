<?php

namespace BristolianTest;

use Bristolian\Page;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class PageTest extends BaseTestCase
{
    private const DEFAULT_QR_MESSAGE = "Show this QR code to someone, and they can scan it with the camera in their device";

    public function teardown(): void
    {
        Page::setQrShareMessage(self::DEFAULT_QR_MESSAGE);
        parent::teardown();
    }

    /**
     * @covers \Bristolian\Page::getQrShareMessage
     */
    public function testGetQrShareMessage_returns_default()
    {
        $result = Page::getQrShareMessage();

        $this->assertSame(self::DEFAULT_QR_MESSAGE, $result);
    }

    /**
     * @covers \Bristolian\Page::setQrShareMessage
     * @covers \Bristolian\Page::getQrShareMessage
     */
    public function testSetQrShareMessage_changes_message()
    {
        $custom = "Custom QR share message for testing";

        Page::setQrShareMessage($custom);

        $this->assertSame($custom, Page::getQrShareMessage());
    }

    /**
     * @covers \Bristolian\Page::setQrShareMessage
     * @covers \Bristolian\Page::getQrShareMessage
     */
    public function testSetQrShareMessage_with_empty_string()
    {
        Page::setQrShareMessage("");

        $this->assertSame("", Page::getQrShareMessage());
    }
}
