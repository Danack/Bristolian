<?php

declare(strict_types=1);

namespace Bristolian\Model\TinnedFish;

use Bristolian\ToArray;

/**
 * Error information for product lookup failures.
 */
class ProductError
{
    use ToArray;

    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly ?string $barcode = null,
        public readonly ?string $details = null
    ) {
    }

    public static function productNotFound(string $barcode): self
    {
        return new self(
            code: 'PRODUCT_NOT_FOUND',
            message: "Product not found for barcode: $barcode",
            barcode: $barcode
        );
    }

    public static function invalidBarcode(string $barcode): self
    {
        return new self(
            code: 'INVALID_BARCODE',
            message: 'Invalid barcode format. Expected EAN/UPC/GTIN format (8-13 digits).',
            barcode: $barcode
        );
    }

    public static function externalApiError(string $barcode, string $details): self
    {
        return new self(
            code: 'EXTERNAL_API_ERROR',
            message: 'Failed to fetch product data from external API',
            barcode: $barcode,
            details: $details
        );
    }

    public static function internalError(): self
    {
        return new self(
            code: 'INTERNAL_ERROR',
            message: 'An internal server error occurred'
        );
    }
}
