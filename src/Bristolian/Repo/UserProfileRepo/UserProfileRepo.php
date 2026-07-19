<?php

namespace Bristolian\Repo\UserProfileRepo;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Attribute\WritesTable;
use Bristolian\Database\user_display_name;
use Bristolian\Database\user_profile;
use Bristolian\Model\Generated\UserDisplayName;
use Bristolian\Model\Types\UserProfileWithDisplayName;

interface UserProfileRepo
{
    /**
     * Get the current user profile including latest display name
     * Or a default blank profile.
     */
    #[ReadsTable(user_display_name::class)]
    #[ReadsTable(user_profile::class)]
    public function getUserProfile(string $user_id): UserProfileWithDisplayName;

    /**
     * Get all display names for a user, ordered by version descending (newest first)
     * @return UserDisplayName[]
     */
    #[ReadsTable(user_display_name::class)]
    public function getDisplayNameHistory(string $user_id): array;

    /**
     * Update user profile (display name, about me, etc.)
     * Display name change creates a new versioned entry.
     * Other fields are updated in place.
     */
    #[WritesTable(user_display_name::class)]
    #[WritesTable(user_profile::class)]
    #[ReadsTable(user_display_name::class)]
    public function updateProfile(string $user_id, \Bristolian\Parameters\UserProfileUpdateParams $params): UserProfileWithDisplayName;

    /**
     * Update just the avatar image ID for a user
     */
    #[WritesTable(user_profile::class)]
    public function updateAvatarImage(string $user_id, string $avatar_image_id): void;
}
