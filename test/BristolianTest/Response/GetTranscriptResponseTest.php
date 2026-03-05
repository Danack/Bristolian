<?php

declare(strict_types=1);

namespace BristolianTest\Response;

use Bristolian\Response\GetTranscriptResponse;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class GetTranscriptResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Response\GetTranscriptResponse::__construct
     * @covers \Bristolian\Response\GetTranscriptResponse::getStatus
     */
    public function test_getStatus_returns_200(): void
    {
        $response = new GetTranscriptResponse('WEBVTT\n\n00:00:00.000 --> 00:00:02.000\nHello');
        $this->assertSame(200, $response->getStatus());
    }

    /**
     * @covers \Bristolian\Response\GetTranscriptResponse::getHeaders
     */
    public function test_getHeaders_returns_content_type_json(): void
    {
        $response = new GetTranscriptResponse('WEBVTT');
        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    /**
     * @covers \Bristolian\Response\GetTranscriptResponse::getBody
     */
    public function test_getBody_returns_json_with_vtt_content(): void
    {
        $vttContent = "WEBVTT\n\n00:00:00.000 --> 00:00:05.000\nFirst line";
        $response = new GetTranscriptResponse($vttContent);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertSame($vttContent, $decoded['data']['vtt_content']);
    }
}
