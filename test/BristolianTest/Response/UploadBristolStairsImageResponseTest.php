<?php

namespace BristolianTest\Response;

use Bristolian\Model\Generated\BristolStairInfo;
use Bristolian\Response\UploadBristolStairsImageResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\UploadBristolStairsImageResponse
 */
class UploadBristolStairsImageResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $stairInfo = new BristolStairInfo(
            id: 1,
            description: 'A nice set of stairs',
            latitude: 51.454513,
            longitude: -2.587910,
            stored_stair_image_file_id: 'image-456',
            steps: 42,
            is_deleted: 0,
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable()
        );
        $response = new UploadBristolStairsImageResponse($stairInfo);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $stairInfo = new BristolStairInfo(
            id: 1,
            description: 'A nice set of stairs',
            latitude: 51.454513,
            longitude: -2.587910,
            stored_stair_image_file_id: 'image-456',
            steps: 42,
            is_deleted: 0,
            created_at: new \DateTimeImmutable(),
            updated_at: new \DateTimeImmutable()
        );
        $response = new UploadBristolStairsImageResponse($stairInfo);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsStairInfo()
    {
        $createdAt = new \DateTimeImmutable('2024-01-15 12:00:00');
        $updatedAt = new \DateTimeImmutable('2024-01-15 12:00:00');
        $stairInfo = new BristolStairInfo(
            id: 1,
            description: 'A nice set of stairs',
            latitude: 51.454513,
            longitude: -2.587910,
            stored_stair_image_file_id: 'image-456',
            steps: 42,
            is_deleted: 0,
            created_at: $createdAt,
            updated_at: $updatedAt
        );
        $response = new UploadBristolStairsImageResponse($stairInfo);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('stair_info', $decoded['data']);
        $this->assertSame(1, $decoded['data']['stair_info']['id']);
        $this->assertSame('A nice set of stairs', $decoded['data']['stair_info']['description']);
        $this->assertSame(42, $decoded['data']['stair_info']['steps']);
    }
}
