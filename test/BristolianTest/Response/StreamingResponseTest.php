<?php

namespace BristolianTest\Response;

use Bristolian\Exception\BristolianResponseException;
use Bristolian\Response\StreamingResponse;
use BristolianTest\BaseTestCase;
use Psr\Http\Message\StreamInterface;

/**
 * @covers \Bristolian\Response\StreamingResponse
 */
class StreamingResponseTest extends BaseTestCase
{
    public function testWorksWithValidPdfFile()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        
        $response = new StreamingResponse($filepath);
        
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetStatusCodeReturns200()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        
        $response = new StreamingResponse($filepath);
        
        $this->assertSame(200, $response->getStatusCode());
    }

    public function testGetBodyStreamReturnsStreamInterface()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        
        $response = new StreamingResponse($filepath);
        $stream = $response->getBodyStream();
        
        $this->assertInstanceOf(StreamInterface::class, $stream);
    }

    public function testGetBodyStreamContainsFileContents()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        
        $response = new StreamingResponse($filepath);
        $stream = $response->getBodyStream();
        
        $expectedContents = \Safe\file_get_contents($filepath);
        $actualContents = $stream->getContents();
        
        $this->assertSame($expectedContents, $actualContents);
    }

    public function testGetHeadersContainsContentType()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        
        $response = new StreamingResponse($filepath);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/pdf', $headers['Content-Type']);
    }

    public function testGetHeadersWithCustomHeaders()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        $customHeaders = [
            'X-Custom-Header' => 'custom-value',
            'Cache-Control' => 'max-age=3600'
        ];
        
        $response = new StreamingResponse($filepath, $customHeaders);
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertArrayHasKey('X-Custom-Header', $headers);
        $this->assertArrayHasKey('Cache-Control', $headers);
        $this->assertSame('custom-value', $headers['X-Custom-Header']);
        $this->assertSame('max-age=3600', $headers['Cache-Control']);
    }

    public function testCustomHeadersOverrideDefaults()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        $customHeaders = [
            'Content-Type' => 'application/octet-stream'
        ];
        
        $response = new StreamingResponse($filepath, $customHeaders);
        $headers = $response->getHeaders();
        
        // Custom header should override the default Content-Type
        $this->assertSame('application/octet-stream', $headers['Content-Type']);
    }

    public function testThrowsExceptionForNonExistentFile()
    {
        $this->expectException(BristolianResponseException::class);
        $this->expectExceptionMessageMatchesTemplateString(
            BristolianResponseException::FAILED_TO_OPEN_FILE
        );
        
        new StreamingResponse('/path/to/nonexistent/file.pdf');
    }

    public function testThrowsExceptionForInvalidPath()
    {
        $this->expectException(\Bristolian\Exception\BristolianException::class);
        $this->expectExceptionMessage('Unknown file type');
        
        new StreamingResponse('');
    }

    public function testWorksWithTextFile()
    {
        // Create a temporary text file for testing
        $filepath = sys_get_temp_dir() . '/test_streaming_' . uniqid() . '.txt';
        file_put_contents($filepath, 'Test content');
        
        try {
            $response = new StreamingResponse($filepath);
            
            $this->assertSame(200, $response->getStatusCode());
            $headers = $response->getHeaders();
            
            $this->assertArrayHasKey('Content-Type', $headers);
            $this->assertSame('text/plain', $headers['Content-Type']);
        } finally {
            // Clean up
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    public function testStreamCanBeReadMultipleTimes()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        
        $response = new StreamingResponse($filepath);
        $stream = $response->getBodyStream();
        
        // Read once
        $contents1 = $stream->getContents();
        
        // Rewind and read again
        $stream->rewind();
        $contents2 = $stream->getContents();
        
        $this->assertSame($contents1, $contents2);
    }

    public function testMultipleHeadersWork()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        $customHeaders = [
            'X-Header-1' => 'value1',
            'X-Header-2' => 'value2',
            'X-Header-3' => 'value3'
        ];
        
        $response = new StreamingResponse($filepath, $customHeaders);
        $headers = $response->getHeaders();
        
        $this->assertCount(4, $headers); // 3 custom + 1 Content-Type
        $this->assertSame('value1', $headers['X-Header-1']);
        $this->assertSame('value2', $headers['X-Header-2']);
        $this->assertSame('value3', $headers['X-Header-3']);
    }

    public function testStreamIsReadable()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        
        $response = new StreamingResponse($filepath);
        $stream = $response->getBodyStream();
        
        $this->assertTrue($stream->isReadable());
    }

    public function testGetHeadersReturnsArray()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        
        $response = new StreamingResponse($filepath);
        $headers = $response->getHeaders();

        // TODO - add useful assertions.
//        $this->assertIsArray($headers);
    }

    public function testWithEmptyCustomHeaders()
    {
        $filepath = __DIR__ . "/../../sample.pdf";
        
        $response = new StreamingResponse($filepath, []);
        $headers = $response->getHeaders();
        
        // Should still have Content-Type even with empty custom headers
        $this->assertCount(1, $headers);
        $this->assertArrayHasKey('Content-Type', $headers);
    }
}
