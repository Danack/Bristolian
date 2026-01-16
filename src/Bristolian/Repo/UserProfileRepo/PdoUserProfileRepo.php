<?php

namespace Bristolian\Repo\UserProfileRepo;

use Bristolian\Database\user_display_name;
use Bristolian\Database\user_profile;
use Bristolian\Model\Types\UserProfileWithDisplayName;
use Bristolian\PdoSimple\PdoSimple;
use Bristolian\Model\Generated\UserDisplayName;
use Bristolian\Model\Generated\UserProfile;

class PdoUserProfileRepo implements UserProfileRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    public function getUserProfile(string $user_id): UserProfileWithDisplayName|null
    {
        // Get the latest display name
        $display_name_sql = user_display_name::SELECT;
        $display_name_sql .= " WHERE user_id = :user_id ORDER BY version DESC LIMIT 1";
        
        $display_name = $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $display_name_sql,
            [':user_id' => $user_id],
            UserDisplayName::class
        );

        // Get profile info (avatar, about me)
        $profile_sql = user_profile::SELECT;
        $profile_sql .= " WHERE user_id = :user_id";
        
        $user_profile = $this->pdo_simple->fetchOneAsObjectOrNullConstructor(
            $profile_sql,
            [':user_id' => $user_id],
            UserProfile::class
        );

        // If no profile data exists, create default
        if ($user_profile === null) {
            $user_profile =  new UserProfile(
                user_id: $user_id,
                avatar_image_id: null,
                about_me: null,
                created_at: new \DateTimeImmutable(),
                updated_at: new \DateTimeImmutable()
            );
        }

        return new UserProfileWithDisplayName(
            $user_profile,
            $display_name
        );
    }

    public function getDisplayNameHistory(string $user_id): array
    {
        $sql = user_display_name::SELECT;
        $sql .= " WHERE user_id = :user_id ORDER BY version DESC";

        $params = [':user_id' => $user_id];

        return $this->pdo_simple->fetchAllAsObjectConstructor(
            $sql,
            $params,
            UserDisplayName::class
        );
    }

    public function updateProfile(string $user_id, \Bristolian\Parameters\UserProfileUpdateParams $params): UserProfileWithDisplayName
    {
        // 1. Insert new display name version
        $display_name_sql = <<< SQL
INSERT INTO user_display_name 
  (user_id, display_name, version)
SELECT 
  :user_id,
  :display_name,
  COALESCE(MAX(version), 0) + 1
FROM 
  user_display_name
WHERE 
  user_id = :user_id_for_select
SQL;

        $display_name_params = [
            ':user_id' => $user_id,
            ':display_name' => $params->display_name,
            ':user_id_for_select' => $user_id
        ];

        $this->pdo_simple->insert($display_name_sql, $display_name_params);

        // 2. Upsert user_profile (INSERT ... ON DUPLICATE KEY UPDATE)
        $profile_sql = <<< SQL
INSERT INTO user_profile 
  (user_id, about_me)
VALUES 
  (:user_id, :about_me)
ON DUPLICATE KEY UPDATE
  about_me = VALUES(about_me)
SQL;

        $profile_params = [
            ':user_id' => $user_id,
            ':about_me' => $params->about_me
        ];

        $this->pdo_simple->execute($profile_sql, $profile_params);

        // 3. Fetch and return the complete updated profile
        $result = $this->getUserProfile($user_id);

        if ($result === null) {
            throw new \Bristolian\Exception\BristolianException(
                "Failed to fetch updated profile for user: " . $user_id
            );
        }

        return $result;
    }

    public function updateAvatarImage(string $user_id, string $avatar_image_id): void
    {
        // Upsert the avatar image ID
        $sql = <<< SQL
INSERT INTO user_profile 
  (user_id, avatar_image_id)
VALUES 
  (:user_id, :avatar_image_id)
ON DUPLICATE KEY UPDATE
  avatar_image_id = VALUES(avatar_image_id)
SQL;

        $params = [
            ':user_id' => $user_id,
            ':avatar_image_id' => $avatar_image_id
        ];

        $this->pdo_simple->execute($sql, $params);
    }
}

