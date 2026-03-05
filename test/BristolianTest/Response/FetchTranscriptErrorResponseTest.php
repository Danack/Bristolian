<?php

declare(strict_types=1);

namespace BristolianTest\Response;

use Bristolian\Response\FetchTranscriptErrorResponse;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FetchTranscriptErrorResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Response\FetchTranscriptErrorResponse::__construct
     * @covers \Bristolian\Response\FetchTranscriptErrorResponse::getStatus
     */
    public function test_getStatus_returns_400(): void
    {
        $response = new FetchTranscriptErrorResponse('Transcript not available');
        $this->assertSame(400, $response->getStatus());
    }

    /**
     * @covers \Bristolian\Response\FetchTranscriptErrorResponse::getHeaders
     */
    public function test_getHeaders_returns_content_type_json(): void
    {
        $response = new FetchTranscriptErrorResponse('Transcript not available');
        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    /**
     * @covers \Bristolian\Response\FetchTranscriptErrorResponse::getBody
     */
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
