<?php

declare(strict_types = 1);

namespace Bristolian\Parameters\PropertyType;

use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\CheckOnlyAllowedCharacters;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\MinLength;

/**
 * Property type for EAN/UPC/GTIN barcode validation.
 * Barcodes must be 8-13 digits.
 */
#[\Attribute]
class Barcode implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetString(),
            new MinLength(8),
            new MaxLength(13),
            new CheckOnlyAllowedCharacters('0-9'),
        );
    }
}
