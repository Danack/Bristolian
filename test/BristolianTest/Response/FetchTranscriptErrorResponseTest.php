<?php

declare(strict_types=1);

namespace BristolianTest\Response;

use Bristolian\Response\FetchTranscriptErrorResponse;
use BristolianTest\BaseTestCase;
use function Safe\json_decode;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(\Bristolian\Response\FetchTranscriptErrorResponse::class, '__construct')]
#[CoversMethod(\Bristolian\Response\FetchTranscriptErrorResponse::class, 'getBody')]
#[CoversMethod(\Bristolian\Response\FetchTranscriptErrorResponse::class, 'getHeaders')]
#[CoversMethod(\Bristolian\Response\FetchTranscriptErrorResponse::class, 'getStatus')]

class FetchTranscriptErrorResponseTest extends BaseTestCase
{
    public function test_getStatus_returns_400(): void
    {
        $response = new FetchTranscriptErrorResponse('Transcript not available');
        $this->assertSame(400, $response->getStatus());
    }

    public function test_getHeaders_returns_content_type_json(): void
    {
        $response = new FetchTranscriptErrorResponse('Transcript not available');
        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function test_getBody_returns_json_with_error_message(): void
    {
        $errorMessage = 'YouTube returned 404 for this video';
        $response = new FetchTranscriptErrorResponse($errorMessage);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('error', $decoded['result']);
        $this->assertSame($errorMessage, $decoded['error']);
    }
}
