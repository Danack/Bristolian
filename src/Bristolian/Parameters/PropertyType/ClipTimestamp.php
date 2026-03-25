<?php

declare(strict_types=1);

namespace Bristolian\Parameters\PropertyType;

use Bristolian\Parameters\ProcessRule\ClipEndTimeAfterStartTime;
use Bristolian\Parameters\ProcessRule\ParseClipTimestampToSeconds;
use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\MinLength;
use DataType\ProcessRule\RangeIntValue;

#[\Attribute]
class ClipTimestamp implements HasInputType
{
    /** Matches room videos UI: allow short strings like "1:15" or plain seconds. */
    private const INPUT_MAX_LENGTH = 32;

    /**
     * @param string $name Input key (e.g. start_time, end_time).
     * @param string|null $end_must_be_after_input_with_name When set (e.g. on end_time), end seconds must be greater than this other input's processed seconds (e.g. start_time).
     */
    public function __construct(
        private string $name,
        private ?string $end_must_be_after_input_with_name = null
    ) {
    }

    public function getInputType(): InputType
    {
        $rules = [
            new GetString(),
            new MinLength(1),
            new MaxLength(self::INPUT_MAX_LENGTH),
            new ParseClipTimestampToSeconds(),
            new RangeIntValue(ClipSeconds::MIN_SECONDS, ClipSeconds::MAX_SECONDS),
        ];
        if ($this->end_must_be_after_input_with_name !== null) {
            $rules[] = new ClipEndTimeAfterStartTime($this->end_must_be_after_input_with_name);
        }

        return new InputType($this->name, ...$rules);
    }
}
