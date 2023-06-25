<?php

namespace Bristolian\DataType;

use DataType\ExtractRule\GetStringOrDefault;
use DataType\InputType;
use DataType\HasInputType;
use DataType\ExtractRule\GetString;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\StartsWithString;
use DataType\ProcessRule\Trim;
use DataType\ProcessRule\SkipIfNull;
use DataType\ProcessRule\NullIfEmpty;

#[\Attribute]
class FacebookUrlOrNull implements HasInputType
{
    public function __construct(
        private string $name
    ) {
    }

    public function getInputType(): InputType
    {
        return new InputType(
            $this->name,
            new GetStringOrDefault(null),
            new SkipIfNull(),
            new Trim(),
            new NullIfEmpty(),
            new SkipIfNull(),
            new MinLength(1),
            new MaxLength(256),
            new StartsWithString("https://www.facebook.com/"),
        );
    }
}
