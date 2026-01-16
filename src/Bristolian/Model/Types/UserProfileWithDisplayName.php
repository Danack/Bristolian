<?php

namespace Bristolian\Model\Types;

use Bristolian\ToArray;
use Bristolian\Model\Generated\UserDisplayName;
use Bristolian\Model\Generated\UserProfile;


/**
 * Combines UserProfile (non-versioned data) with the latest UserDisplayName (versioned)
 */
class UserProfileWithDisplayName
{
    use ToArray;

    public function __construct(
        public readonly UserProfile $user_profile,
        public readonly UserDisplayName|null $display_name
    ) {
    }

    public function getUserId(): string
    {
        return $this->user_profile->user_id;
    }

    public function getDisplayName(): string
    {
        return $this->display_name->display_name ?? '';
    }

    public function getAboutMe(): string|null
    {
        return $this->user_profile->about_me;
    }

    public function getAvatarImageId(): string|null
    {
        return $this->user_profile->avatar_image_id;
    }
}
