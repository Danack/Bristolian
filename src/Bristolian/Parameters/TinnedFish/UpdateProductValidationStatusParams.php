<?php

declare(strict_types = 1);

namespace Bristolian\Parameters\TinnedFish;

use Bristolian\Model\TinnedFish\ValidationStatus;
use Bristolian\Parameters\PropertyType\BasicPhpEnumType;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Parameters for updating product validation status API endpoint.
 */
class UpdateProductValidationStatusParams implements DataType, StaticFactory
{
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicPhpEnumType('validation_status', ValidationStatus::class)]
        public readonly ValidationStatus $validation_status,
    ) {
    }
}
