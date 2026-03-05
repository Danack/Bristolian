<?php

declare(strict_types=1);

namespace BristolianTest\Response;

use Bristolian\Model\Generated\RoomVideoTranscript;
use Bristolian\Model\Types\RoomVideoTranscriptList;
use Bristolian\Response\GetTranscriptsResponse;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class GetTranscriptsResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Response\GetTranscriptsResponse::__construct
     * @covers \Bristolian\Response\GetTranscriptsResponse::getStatus
     */
    public function test_getStatus_returns_200(): void
    {
        $transcriptList = new RoomVideoTranscriptList([]);
        $response = new GetTranscriptsResponse($transcriptList);
        $this->assertSame(200, $response->getStatus());
    }

    /**
     * @covers \Bristolian\Response\GetTranscriptsResponse::getHeaders
     */
    public function test_getHeaders_returns_content_type_json(): void
    {
        $transcriptList = new RoomVideoTranscriptList([]);
        $response = new GetTranscriptsResponse($transcriptList);
        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    /**
     * @covers \Bristolian\Response\GetTranscriptsResponse::getBody
     */
    public function test_getBody_returns_json_with_empty_transcripts(): void
    {
        $transcriptList = new RoomVideoTranscriptList([]);
        $response = new GetTranscriptsResponse($transcriptList);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertArrayHasKey('transcripts', $decoded['data']);
        $this->assertSame([], $decoded['data']['transcripts']);
    }

    /**
     * @covers \Bristolian\Response\GetTranscriptsResponse::getBody
     */
    public function test_getBody_returns_json_with_transcripts_list(): void
    {
        $transcript = new RoomVideoTranscript(
            id: 't1',
            room_video_id: 'rv1',
            transcript_number: 1,
            language: 'en',
            vtt_content: 'WEBVTT',
            created_at: new \DateTimeImmutable('2024-01-01 12:00:00')
        );
        $transcriptList = new RoomVideoTranscriptList([$transcript]);
        $response = new GetTranscriptsResponse($transcriptList);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertCount(1, $decoded['data']['transcripts']);
        $this->assertSame('t1', $decoded['data']['transcripts'][0]['id']);
        $this->assertSame('rv1', $decoded['data']['transcripts'][0]['room_video_id']);
        $this->assertSame(1, $decoded['data']['transcripts'][0]['transcript_number']);
        $this->assertSame('en', $decoded['data']['transcripts'][0]['language']);
        $this->assertSame('WEBVTT', $decoded['data']['transcripts'][0]['vtt_content']);
    }
}
