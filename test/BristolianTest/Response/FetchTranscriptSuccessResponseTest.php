<?php

declare(strict_types=1);

namespace BristolianTest\Response;

use Bristolian\Response\FetchTranscriptSuccessResponse;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class FetchTranscriptSuccessResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Response\FetchTranscriptSuccessResponse::__construct
     * @covers \Bristolian\Response\FetchTranscriptSuccessResponse::getStatus
     */
    public function test_getStatus_returns_200(): void
    {
        $response = new FetchTranscriptSuccessResponse('transcript_1', 1);
        $this->assertSame(200, $response->getStatus());
    }

    /**
     * @covers \Bristolian\Response\FetchTranscriptSuccessResponse::getHeaders
     */
    public function test_getHeaders_returns_content_type_json(): void
    {
        $response = new FetchTranscriptSuccessResponse('transcript_1', 1);
        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    /**
     * @covers \Bristolian\Response\FetchTranscriptSuccessResponse::getBody
     */
    public function test_getBody_returns_json_with_transcript_id_and_number(): void
    {
        $transcriptId = 'transcript_abc';
        $transcriptNumber = 2;
        $response = new FetchTranscriptSuccessResponse($transcriptId, $transcriptNumber);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertSame($transcriptId, $decoded['data']['transcript_id']);
        $this->assertSame($transcriptNumber, $decoded['data']['transcript_number']);
    }

    /**
     * @covers \Bristolian\Response\FetchTranscriptSuccessResponse::getBody
     */
    public function test_getBody_accepts_null_transcript_number(): void
    {
        $response = new FetchTranscriptSuccessResponse('transcript_xyz', null);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertSame('success', $decoded['result']);
        $this->assertSame('transcript_xyz', $decoded['data']['transcript_id']);
        $this->assertNull($decoded['data']['transcript_number']);
    }
}
