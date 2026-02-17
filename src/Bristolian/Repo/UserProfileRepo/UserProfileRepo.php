<?php

namespace Bristolian\Repo\UserProfileRepo;

use Bristolian\Model\Generated\UserDisplayName;
use Bristolian\Model\Types\UserProfileWithDisplayName;

interface UserProfileRepo
{
    /**
     * Get the current user profile including latest display name
     * Or a default blank profile.
     */
    public function getUserProfile(string $user_id): UserProfileWithDisplayName;

    /**
     * Get all display names for a user, ordered by version descending (newest first)
     * @return UserDisplayName[]
     */
    public function getDisplayNameHistory(string $user_id): array;

    /**
     * Update user profile (display name, about me, etc.)
     * Display name change creates a new versioned entry.
     * Other fields are updated in place.
     */
    public function updateProfile(string $user_id, \Bristolian\Parameters\UserProfileUpdateParams $params): UserProfileWithDisplayName;

    /**
     * Update just the avatar image ID for a user
     */
    public function updateAvatarImage(string $user_id, string $avatar_image_id): void;
}
