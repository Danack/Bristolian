<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\FoiRequest;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FoiRequestTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\FoiRequest
     */
    public function testCreate()
    {
        $foiRequestId = 'foi-123';
        $text = 'Request text';
        $url = 'https://example.com/request';
        $description = 'A test FOI request';

        $foiRequest = new FoiRequest($foiRequestId, $text, $url, $description);

        $this->assertSame($foiRequestId, $foiRequest->getFoiRequestId());
        $this->assertSame($text, $foiRequest->getText());
        $this->assertSame($url, $foiRequest->getUrl());
        $this->assertSame($description, $foiRequest->getDescription());
    }
}
