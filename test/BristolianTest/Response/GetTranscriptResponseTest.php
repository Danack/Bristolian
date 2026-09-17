<?php

declare(strict_types=1);

namespace BristolianTest\Response;

use Bristolian\Response\GetTranscriptResponse;
use BristolianTest\BaseTestCase;
use function Safe\json_decode;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Response\GetTranscriptResponse::class, '__construct')]
#[CoversMethod(\Bristolian\Response\GetTranscriptResponse::class, 'getBody')]
#[CoversMethod(\Bristolian\Response\GetTranscriptResponse::class, 'getHeaders')]
#[CoversMethod(\Bristolian\Response\GetTranscriptResponse::class, 'getStatus')]

class GetTranscriptResponseTest extends BaseTestCase
{
    public function test_getStatus_returns_200(): void
    {
        $response = new GetTranscriptResponse('WEBVTT\n\n00:00:00.000 --> 00:00:02.000\nHello');
        $this->assertSame(200, $response->getStatus());
    }

    public function test_getHeaders_returns_content_type_json(): void
    {
        $response = new GetTranscriptResponse('WEBVTT');
        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

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
