<?php

declare(strict_types=1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\ClipTimestamp;
use Bristolian\Parameters\PropertyType\OptionalAddVideoDescription;
use Bristolian\Parameters\PropertyType\OptionalAddVideoTitle;
use Bristolian\Parameters\PropertyType\YoutubeVideoId;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * CLI (and similar) request for adding a time-bounded YouTube clip to a room.
 */
class AddVideoClipParam implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[YoutubeVideoId('url')]
        public readonly string $youtube_video_id,
        #[ClipTimestamp('start_time')]
        public readonly int $start_seconds,
        #[ClipTimestamp('end_time', 'start_time')]
        public readonly int $end_seconds,
        #[OptionalAddVideoTitle('title')]
        public readonly ?string $title,
        #[OptionalAddVideoDescription('description')]
        public readonly ?string $description,
    ) {
    }
}
