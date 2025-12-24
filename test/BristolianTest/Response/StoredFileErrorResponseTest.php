<?php

namespace BristolianTest\Response;

use Bristolian\Response\StoredFileErrorResponse;
use BristolianTest\BaseTestCase;
use SlimDispatcher\Response\StubResponse;

/**
 * @covers \Bristolian\Response\StoredFileErrorResponse
 */
class StoredFileErrorResponseTest extends BaseTestCase
{
    public function testReturns500StatusCode()
    {
        $filename = 'path/to/missing/file.pdf';
        $response = new StoredFileErrorResponse($filename);
        
        $this->assertSame(500, $response->getStatus());
    }

    public function testImplementsStubResponseInterface()
    {
        $filename = 'path/to/missing/file.pdf';
        $response = new StoredFileErrorResponse($filename);
        
        $this->assertInstanceOf(StubResponse::class, $response);
    }

    public function testGetBodyContainsErrorMessage()
    {
        $filename = 'path/to/missing/file.pdf';
        $response = new StoredFileErrorResponse($filename);
        
        $body = $response->getBody();
        
        $this->assertStringContainsString('Failed to retrieve stored file', $body);
        $this->assertStringContainsString($filename, $body);
        $this->assertStringContainsString('contact an administrator', $body);
    }

    public function testGetBodyContainsFilename()
    {
        $filename = 'specific/test/file.jpg';
        $response = new StoredFileErrorResponse($filename);
        
        $body = $response->getBody();
        
        $this->assertStringContainsString($filename, $body);
    }

    public function testGetHeadersReturnsEmptyArrayByDefault()
    {
        $filename = 'path/to/file.pdf';
        $response = new StoredFileErrorResponse($filename);
        
        $headers = $response->getHeaders();

        $this->assertEmpty($headers);
    }

    public function testGetHeadersReturnsProvidedHeaders()
    {
        $filename = 'path/to/file.pdf';
        $customHeaders = [
            'X-Custom-Header' => 'custom-value',
            'X-Error-Code' => 'FILE_NOT_FOUND'
        ];
        
        $response = new StoredFileErrorResponse($filename, $customHeaders);
        
        $headers = $response->getHeaders();
        
        $this->assertSame($customHeaders, $headers);
        $this->assertArrayHasKey('X-Custom-Header', $headers);
        $this->assertSame('custom-value', $headers['X-Custom-Header']);
    }

    public function testWithMultipleCustomHeaders()
    {
        $filename = 'test/file.png';
        $headers = [
            'X-Error-Type' => 'storage',
            'X-Request-Id' => 'abc123',
            'Cache-Control' => 'no-cache'
        ];
        
        $response = new StoredFileErrorResponse($filename, $headers);
        
        $returnedHeaders = $response->getHeaders();
        
        $this->assertCount(3, $returnedHeaders);
        $this->assertSame('storage', $returnedHeaders['X-Error-Type']);
        $this->assertSame('abc123', $returnedHeaders['X-Request-Id']);
        $this->assertSame('no-cache', $returnedHeaders['Cache-Control']);
    }

    public function testWithLongFilename()
    {
        $filename = 'very/long/path/to/some/deeply/nested/directory/structure/file.pdf';
        $response = new StoredFileErrorResponse($filename);
        
        $body = $response->getBody();
        
        $this->assertStringContainsString($filename, $body);
    }

    public function testWithSpecialCharactersInFilename()
    {
        $filename = 'path/to/file-with-special_chars (2023).pdf';
        $response = new StoredFileErrorResponse($filename);
        
        $body = $response->getBody();
        
        $this->assertStringContainsString($filename, $body);
    }

    public function testBodyIsString()
    {
        $filename = 'test/file.pdf';
        $response = new StoredFileErrorResponse($filename);
        
        $body = $response->getBody();

        // TODO - write useful assertions
//        $this->assertIsString($body);
    }

    public function testDifferentFilenamesProduceDifferentBodies()
    {
        $filename1 = 'file1.pdf';
        $filename2 = 'file2.jpg';
        
        $response1 = new StoredFileErrorResponse($filename1);
        $response2 = new StoredFileErrorResponse($filename2);
        
        $body1 = $response1->getBody();
        $body2 = $response2->getBody();
        
        $this->assertNotSame($body1, $body2);
        $this->assertStringContainsString($filename1, $body1);
        $this->assertStringContainsString($filename2, $body2);
    }
}

