<?php

namespace BristolianTest\Model;

use Bristolian\Model\Types\FoiRequest;
use Bristolian\Parameters\FoiRequestParams;
use BristolianTest\BaseTestCase;
use VarMap\ArrayVarMap;

/**
 * @coversNothing
 */
class FoiRequestTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\Types\FoiRequest
     */
    public function testCreate(): void
    {
        $foiRequestId = 'foi-123';
        $text = 'Request text';
        $url = 'https://example.com/request';
        $description = 'A test FOI request';
        $createdAt = new \DateTimeImmutable('2024-01-15 12:00:00');

        $foiRequest = new FoiRequest($foiRequestId, $text, $url, $description, $createdAt);

        $this->assertSame($foiRequestId, $foiRequest->getFoiRequestId());
        $this->assertSame($text, $foiRequest->getText());
        $this->assertSame($url, $foiRequest->getUrl());
        $this->assertSame($description, $foiRequest->getDescription());
        $this->assertSame($createdAt, $foiRequest->getCreatedAt());
    }

    /**
     * @covers \Bristolian\Model\Types\FoiRequest
     */
    public function testFromParam(): void
    {
        $uuid = 'foi-uuid-456';
        $foiParam = FoiRequestParams::createFromVarMap(new ArrayVarMap([
            'text' => 'Request from param',
            'url' => 'https://example.com/foi',
            'description' => 'Description from param',
        ]));

        $foiRequest = FoiRequest::fromParam($uuid, $foiParam);

        $this->assertSame($uuid, $foiRequest->getFoiRequestId());
        $this->assertSame('Request from param', $foiRequest->getText());
        $this->assertSame('https://example.com/foi', $foiRequest->getUrl());
        $this->assertSame('Description from param', $foiRequest->getDescription());
        $this->assertInstanceOf(\DateTimeInterface::class, $foiRequest->getCreatedAt());
    }
}
