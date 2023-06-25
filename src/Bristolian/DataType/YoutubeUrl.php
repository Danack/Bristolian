<?php

namespace Bristolian\DataType;

use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetString;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\StartsWithString;

#[\Attribute]
class YoutubeUrl implements HasInputType
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
            new MinLength(1),
            new MaxLength(256),
            new StartsWithString("https://www.youtube.com/"),
        );
    }
}
