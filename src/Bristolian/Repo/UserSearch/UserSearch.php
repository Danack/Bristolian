<?php

namespace Bristolian\Repo\UserSearch;

interface UserSearch
{
    public const MAX_SEARCH_RESULTS = 50;
    /**
     * Searches users by username. Used on front end by users + admins
     * to find users.
     * @param $username
     * @return mixed
     */
    public function searchUsernamesByPrefix(string $username);
}
