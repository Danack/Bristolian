<?php

declare(strict_types=1);

namespace Bristolian\Parameters;

use Bristolian\Parameters\PropertyType\ClipDescription;
use Bristolian\Parameters\PropertyType\ClipTitle;
use Bristolian\Parameters\PropertyType\YoutubeVideoId;
use Bristolian\StaticFactory;
use DataType\Create\CreateFromArray;
use DataType\Create\CreateFromRequest;
use DataType\Create\CreateFromVarMap;
use DataType\DataType;
use DataType\GetInputTypesFromAttributes;

/**
 * Request body for adding a YouTube video to a room.
 */
class AddVideoParam implements DataType, StaticFactory
{
    use CreateFromArray;
    use CreateFromRequest;
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[YoutubeVideoId('url')]
        public readonly string $youtube_video_id,
        #[ClipTitle('title')]
        public readonly ?string $title,
        #[ClipDescription('description')]
        public readonly ?string $description,
    ) {
    }
}
