<?php

declare(strict_types=1);

namespace Bristolian\Parameters\PropertyType;

use Bristolian\Parameters\ProcessRule\ExtractYoutubeVideoId;
use DataType\ExtractRule\GetString;
use DataType\HasInputType;
use DataType\InputType;
use DataType\ProcessRule\MaxLength;
use DataType\ProcessRule\MinLength;

/**
 * Input type that accepts a YouTube URL or raw video ID and normalises to the 11-character video ID.
 * Fails validation if the value is not a recognised YouTube URL or ID.
 */
#[\Attribute]
class YoutubeVideoId implements HasInputType
{
    /** YouTube video IDs are 11 characters. */
    public const VIDEO_ID_LENGTH = 11;

    /** Reasonable max length for a URL. */
    private const URL_MAX_LENGTH = 2048;

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
            new MaxLength(self::URL_MAX_LENGTH),
            new ExtractYoutubeVideoId()
        );
    }
}
