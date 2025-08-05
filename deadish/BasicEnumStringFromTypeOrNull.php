<?php

namespace deadish;

use DataType\ExtractRule\GetOptionalString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\Enum;
use DataType\ProcessRule\SkipIfNull;

#[\Attribute]
class BasicEnumStringFromTypeOrNull implements HasInputType
{
    /**
     * @var \BackedEnum[]
     */
    private array $enum_values;

    public function __construct(
        private string $name,
        // @phpstan-ignore-next-line property.onlyWritten
        private string $enum_type
    ) {
        $this->enum_values = getEnumCases($enum_type);
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetOptionalString(),
            new SkipIfNull(),
            new Enum($this->enum_values)
        );
    }
}
