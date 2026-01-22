<?php

namespace BristolianTest\Response\TinnedFish;

use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Response\TinnedFish\UpdateProductValidationStatusResponse;
use BristolianTest\BaseTestCase;

/**
 * @covers \Bristolian\Response\TinnedFish\UpdateProductValidationStatusResponse
 */
class UpdateProductValidationStatusResponseTest extends BaseTestCase
{
    public function testGetStatusReturns200()
    {
        $response = new UpdateProductValidationStatusResponse(
            '1234567890123',
            ValidationStatus::VALIDATED_IS_FISH
        );
        
        $this->assertSame(200, $response->getStatus());
    }

    public function testGetHeadersReturnsContentType()
    {
        $response = new UpdateProductValidationStatusResponse(
            '1234567890123',
            ValidationStatus::VALIDATED_IS_FISH
        );
        $headers = $response->getHeaders();
        
        $this->assertArrayHasKey('Content-Type', $headers);
        $this->assertSame('application/json', $headers['Content-Type']);
    }

    public function testGetBodyReturnsValidationStatus()
    {
        $response = new UpdateProductValidationStatusResponse(
            '1234567890123',
            ValidationStatus::VALIDATED_IS_FISH
        );
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertSame('1234567890123', $decoded['barcode']);
        $this->assertSame('validated_is_fish', $decoded['validation_status']);
    }

    public function testGetBodyWithNotValidated()
    {
        $response = new UpdateProductValidationStatusResponse(
            '9876543210987',
            ValidationStatus::NOT_VALIDATED
        );
        $body = $response->getBody();
        
        $decoded = json_decode($body, true);
        $this->assertIsArray($decoded);
        $this->assertSame('not_validated', $decoded['validation_status']);
    }
}
