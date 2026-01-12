<?php

namespace BristolianTest\Model\TinnedFish;

use Bristolian\Model\TinnedFish\ProductError;
use BristolianTest\BaseTestCase;

/**
 * Tests for ProductError model
 *
 * @covers \Bristolian\Model\TinnedFish\ProductError
 */
class ProductErrorTest extends BaseTestCase
{
    /**
     * @covers \Bristolian\Model\TinnedFish\ProductError::productNotFound
     */
    public function test_productNotFound_creates_correct_error(): void
    {
        $error = ProductError::productNotFound('1234567890123');

        $this->assertSame('PRODUCT_NOT_FOUND', $error->code);
        $this->assertStringContainsString('1234567890123', $error->message);
        $this->assertSame('1234567890123', $error->barcode);
        $this->assertNull($error->details);
    }

    /**
     * @covers \Bristolian\Model\TinnedFish\ProductError::invalidBarcode
     */
    public function test_invalidBarcode_creates_correct_error(): void
    {
        $error = ProductError::invalidBarcode('invalid');

        $this->assertSame('INVALID_BARCODE', $error->code);
        $this->assertStringContainsString('8-13 digits', $error->message);
        $this->assertSame('invalid', $error->barcode);
        $this->assertNull($error->details);
    }

    /**
     * @covers \Bristolian\Model\TinnedFish\ProductError::externalApiError
     */
    public function test_externalApiError_creates_correct_error(): void
    {
        $error = ProductError::externalApiError('1234567890123', 'Network timeout');

        $this->assertSame('EXTERNAL_API_ERROR', $error->code);
        $this->assertStringContainsString('external API', $error->message);
        $this->assertSame('1234567890123', $error->barcode);
        $this->assertSame('Network timeout', $error->details);
    }

    /**
     * @covers \Bristolian\Model\TinnedFish\ProductError::internalError
     */
    public function test_internalError_creates_correct_error(): void
    {
        $error = ProductError::internalError();

        $this->assertSame('INTERNAL_ERROR', $error->code);
        $this->assertStringContainsString('internal server error', $error->message);
        $this->assertNull($error->barcode);
        $this->assertNull($error->details);
    }

    /**
     * @covers \Bristolian\Model\TinnedFish\ProductError::__construct
     */
    public function test_constructor_sets_all_fields(): void
    {
        $error = new ProductError(
            code: 'CUSTOM_ERROR',
            message: 'Custom error message',
            barcode: '9876543210987',
            details: 'Some details'
        );

        $this->assertSame('CUSTOM_ERROR', $error->code);
        $this->assertSame('Custom error message', $error->message);
        $this->assertSame('9876543210987', $error->barcode);
        $this->assertSame('Some details', $error->details);
    }
}
