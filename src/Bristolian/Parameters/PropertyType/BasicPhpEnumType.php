<?php

namespace Bristolian\Parameters\PropertyType;

use Bristolian\Parameters\ProcessRule\PhpEnum;
use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;

#[\Attribute]
class BasicPhpEnumType implements HasInputType
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
            new GetString(),
            new PhpEnum($this->enum_type)
        );
    }
}
