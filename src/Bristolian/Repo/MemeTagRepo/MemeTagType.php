<?php

declare(strict_types = 1);

namespace Bristolian\Repo\MemeTagRepo;

/**
 * Types of meme tags.
 * User tags are created and managed by users.
 * System tags (e.g., NSFW, age rating) are managed by the system and cannot be edited by users.
 */
enum MemeTagType: string
{
    case USER_TAG = 'user_tag';
}
