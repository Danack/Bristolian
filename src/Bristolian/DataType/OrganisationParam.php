<?php

namespace Bristolian\DataType;

use DataType\DataType;
use DataType\Create\CreateFromVarMap;
use DataType\GetInputTypesFromAttributes;
use Bristolian\DataType\FacebookUrlOrNull;
use Bristolian\DataType\InstagramUrl;
use Bristolian\DataType\TwitterUrl;
use Bristolian\DataType\YoutubeUrl;

class OrganisationParam implements DataType
{
    use CreateFromVarMap;
    use GetInputTypesFromAttributes;

    public function __construct(
        #[BasicString('name')]
        public readonly string $name,
        #[BasicString('description')]
        public readonly string $description,
        #[Url('homepage_link')]
        public readonly string $homepage_link,
        #[FacebookUrlOrNull('facebook_link')]
        public readonly string $facebook_link,
        #[InstagramUrl('instagram_link')]
        public readonly string $instagram_link,
        #[TwitterUrl('twitter_url')]
        public readonly string $twitter_url,
        #[YoutubeUrl('youtube_link')]
        public readonly string $youtube_link,
    ) {
    }
}
