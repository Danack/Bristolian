<?php

namespace BristolianTest\Response;

use Bristolian\Exception\DataEncodingException;
use Bristolian\Response\GetCspReportsResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\GetCspReportsResponse
 */
class GetCspReportsResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $reports = [
            ['violated-directive' => "script-src 'self'", 'blocked-uri' => 'https://evil.com'],
        ];
        $response = new GetCspReportsResponse(1, $reports);
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $reports = [];
        $response = new GetCspReportsResponse(0, $reports);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsReportsWithCount()
    {
        $reports = [
            ['violated-directive' => "script-src 'self'", 'blocked-uri' => 'https://evil.com'],
            ['violated-directive' => "style-src 'self'", 'blocked-uri' => 'https://bad.com']
        ];
        $response = new GetCspReportsResponse(2, $reports);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertSame(2, $decoded['data']['count']);
        $this->assertArrayHasKey('reports', $decoded['data']);
        $this->assertCount(2, $decoded['data']['reports']);
    }

    public function testGetBodyWithEmptyReports()
    {
        $reports = [];
        $response = new GetCspReportsResponse(0, $reports);
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['result']);
        $this->assertSame(0, $decoded['data']['count']);
        $this->assertCount(0, $decoded['data']['reports']);
    }
}
