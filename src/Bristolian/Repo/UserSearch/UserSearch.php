<?php

namespace Bristolian\Repo\UserSearch;

use Bristolian\Attribute\ReadsTable;
use Bristolian\Database\user_auth_email_password;

interface UserSearch
{
    public const MAX_SEARCH_RESULTS = 50;
    /**
     * Searches users by username. Used on front end by users + admins
     * to find users.
     * @param $username
     * @return mixed
     */
    #[ReadsTable(user_auth_email_password::class)]
    public function searchUsernamesByPrefix(string $username_prefix);
}
