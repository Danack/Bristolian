<?php

namespace Bristolian\Parameters\PropertyType;

use Bristolian\Parameters\ProcessRule\RandomPasswordIfNullOrEmpty;
use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\MinLength;

#[\Attribute]
class PasswordOrRandom implements HasInputType
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
            new RandomPasswordIfNullOrEmpty(16),
            new MinLength(1),
            new MaxLength(256),
        );
    }
}
