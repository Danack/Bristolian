<?php

namespace Bristolian\DataType\PropertyType;

use Bristolian\DataType\ProcessRule\PhpEnum;
use DataType\ExtractRule\GetOptionalString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\SkipIfNull;

#[\Attribute]
class BasicPhpEnumTypeOrNull implements HasInputType
{
    /**
     * @param string $name
     * @param class-string $enum_type
     */
    public function __construct(
        private string $name,
        private string $enum_type
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetOptionalString(),
            new SkipIfNull(),
            new PhpEnum($this->enum_type)
        );
    }
}
