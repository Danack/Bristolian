<?php

declare(strict_types=1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\ClipDescription;
use Bristolian\Parameters\PropertyType\ClipSeconds;
use Bristolian\Parameters\PropertyType\ClipTitle;
use Bristolian\Parameters\PropertyType\RoomVideoId;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Request body for creating a clip from an existing room video.
 */
class CreateClipParam implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[RoomVideoId('room_video_id')]
        public readonly string $room_video_id,
        #[ClipSeconds('start_seconds')]
        public readonly int $start_seconds,
        #[ClipSeconds('end_seconds')]
        public readonly int $end_seconds,
        #[ClipTitle('title')]
        public readonly ?string $title,
        #[ClipDescription('description')]
        public readonly ?string $description,
    ) {
    }
}
