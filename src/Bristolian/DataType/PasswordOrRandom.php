<?php

namespace Bristolian\DataType;

use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetString;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\MaxLength;
use Bristolian\DataType\ProcessRule\RandomPasswordIfNullOrEmpty;

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
