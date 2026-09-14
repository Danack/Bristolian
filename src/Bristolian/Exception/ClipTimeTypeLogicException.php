<?php

declare(strict_types=1);

namespace Bristolian\Exception;

use DataType\Exception\DataTypeLogicException;

/**
 * Thrown when clip start/end process rules receive non-integer values.
 * That indicates a programmer error in the DataType definition, not bad input.
 */
class ClipTimeTypeLogicException extends DataTypeLogicException
{
    public static function endValueMustBeInteger(): self
    {
        return new self("end value must be integer");
    }

    public static function startValueMustBeInteger(): self
    {
        return new self("start value must be integer");
    }
}
