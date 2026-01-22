<?php

namespace BristolianTest\Response;

use Bristolian\Response\GetMemeTagSuggestionsResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\GetMemeTagSuggestionsResponse
 */
class GetMemeTagSuggestionsResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $tags = [['text' => 'funny', 'count' => 5]];
        $response = new GetMemeTagSuggestionsResponse($tags);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $tags = [['text' => 'funny', 'count' => 5]];
        $response = new GetMemeTagSuggestionsResponse($tags);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsTags()
    {
        $tags = [
            ['text' => 'funny', 'count' => 5],
            ['text' => 'meme', 'count' => 10]
        ];
        $response = new GetMemeTagSuggestionsResponse($tags);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('tags', $decoded['data']);
        $this->assertCount(2, $decoded['data']['tags']);
        $this->assertSame('funny', $decoded['data']['tags'][0]['text']);
        $this->assertSame(5, $decoded['data']['tags'][0]['count']);
    }

    public function testGetBodyWithEmptyTags()
    {
        $tags = [];
        $response = new GetMemeTagSuggestionsResponse($tags);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertCount(0, $decoded['data']['tags']);
    }
}
