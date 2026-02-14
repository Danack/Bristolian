<?php

declare(strict_types = 1);

namespace Bristolian\Repo\UserProfileRepo;

use Bristolian\Model\Generated\UserDisplayName;
use Bristolian\Model\Generated\UserProfile;
use Bristolian\Model\Types\UserProfileWithDisplayName;
use Bristolian\Parameters\UserProfileUpdateParams;

/**
 * Fake implementation of UserProfileRepo for testing.
 */
class FakeUserProfileRepo implements UserProfileRepo
{
    /**
     * @var array<string, UserProfile>
     * Keyed by user_id
     */
    private array $profiles = [];

    /**
     * @var array<string, UserDisplayName[]>
     * Keyed by user_id, array of display names ordered by version
     */
    private array $displayNames = [];

    private int $nextDisplayNameId = 1;

    public function getUserProfile(string $user_id): UserProfileWithDisplayName
    {
        // Get profile (create default if doesn't exist, matching PDO behavior)
        $profile = $this->profiles[$user_id] ??  UserProfile::createBlankForUserId($user_id);

        // Get latest display name (highest version)
        $displayName = null;
        if (isset($this->displayNames[$user_id]) && !empty($this->displayNames[$user_id])) {
            $displayNames = $this->displayNames[$user_id];
            // Sort by version desc to get latest
            usort($displayNames, function (UserDisplayName $a, UserDisplayName $b) {
                return $b->version <=> $a->version;
            });
            $displayName = $displayNames[0];
        }

        // PDO implementation always returns UserProfileWithDisplayName (never null)
        return new UserProfileWithDisplayName(
            $profile,
            $displayName
        );
    }

    /**
     * Get all display names for a user, ordered by version descending (newest first)
     * @return UserDisplayName[]
     */
    public function getDisplayNameHistory(string $user_id): array
    {
        if (!isset($this->displayNames[$user_id])) {
            return [];
        }

        $displayNames = $this->displayNames[$user_id];
        // Sort by version desc (newest first)
        usort($displayNames, function (UserDisplayName $a, UserDisplayName $b) {
            return $b->version <=> $a->version;
        });

        return $displayNames;
    }

    /**
     * Update user profile (display name, about me, etc.)
     * Display name change creates a new versioned entry.
     * Other fields are updated in place.
     */
    public function updateProfile(string $user_id, UserProfileUpdateParams $params): UserProfileWithDisplayName
    {
        $now = new \DateTimeImmutable();

        // 1. Create new display name version
        if (!isset($this->displayNames[$user_id])) {
            $this->displayNames[$user_id] = [];
        }

        // Get next version number
        $nextVersion = 1;
        if (!empty($this->displayNames[$user_id])) {
            $maxVersion = 0;
            foreach ($this->displayNames[$user_id] as $dn) {
                if ($dn->version > $maxVersion) {
                    $maxVersion = $dn->version;
                }
            }
            $nextVersion = $maxVersion + 1;
        }

        $newDisplayName = new UserDisplayName(
            id: $this->nextDisplayNameId++,
            user_id: $user_id,
            display_name: $params->display_name,
            version: $nextVersion,
            created_at: $now,
        );

        $this->displayNames[$user_id][] = $newDisplayName;

        // 2. Update or create profile
        $existingProfile = $this->profiles[$user_id] ?? null;
        if ($existingProfile !== null) {
            $this->profiles[$user_id] = new UserProfile(
                user_id: $user_id,
                avatar_image_id: $existingProfile->avatar_image_id,
                about_me: $params->about_me,
                created_at: $existingProfile->created_at,
                updated_at: $now,
            );
        }
        else {
            $this->profiles[$user_id] = new UserProfile(
                user_id: $user_id,
                avatar_image_id: null,
                about_me: $params->about_me,
                created_at: $now,
                updated_at: $now,
            );
        }

        // 3. Return updated profile
        return $this->getUserProfile($user_id);
    }

    /**
     * Update just the avatar image ID for a user
     */
    public function updateAvatarImage(string $user_id, string $avatar_image_id): void
    {
        $now = new \DateTimeImmutable();

        $existingProfile = $this->profiles[$user_id] ?? null;
        if ($existingProfile !== null) {
            $this->profiles[$user_id] = new UserProfile(
                user_id: $user_id,
                avatar_image_id: $avatar_image_id,
                about_me: $existingProfile->about_me,
                created_at: $existingProfile->created_at,
                updated_at: $now,
            );
        }
        else {
            $this->profiles[$user_id] = new UserProfile(
                user_id: $user_id,
                avatar_image_id: $avatar_image_id,
                about_me: null,
                created_at: $now,
                updated_at: $now,
            );
        }
    }
}
