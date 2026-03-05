<?php

declare(strict_types=1);

namespace BristolianTest\Response;

use Bristolian\Response\CreateClipResponse;
use BristolianTest\BaseTestCase;

/**
 * @coversNothing
 */
class CreateClipResponseTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Response\CreateClipResponse::__construct
     * @covers \Bristolian\Response\CreateClipResponse::getStatus
     */
    public function test_getStatus_returns_200(): void
    {
        $response = new CreateClipResponse('room_video_123');
        $this->assertSame(200, $response->getStatus());
    }

    /**
     * @covers \Bristolian\Response\CreateClipResponse::getHeaders
     */
    public function test_getHeaders_returns_content_type_json(): void
    {
        $response = new CreateClipResponse('room_video_123');
        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    /**
     * @covers \Bristolian\Response\CreateClipResponse::getBody
     */
    public function test_getBody_returns_json_with_room_video_id(): void
    {
        $roomVideoId = '550e8400-e29b-41d4-a716-446655440000';
        $response = new CreateClipResponse($roomVideoId);
        $body = $response->getBody();

        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertSame($roomVideoId, $decoded['data']['room_video_id']);
    }
}
