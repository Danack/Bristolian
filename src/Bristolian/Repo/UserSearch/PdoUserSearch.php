<?php

namespace Bristolian\Repo\UserSearch;

use Bristolian\PdoSimple\PdoSimple;

class PdoUserSearch implements UserSearch
{
    public function __construct(private PdoSimple $pdoSimple)
    {
    }

    /**
     * @param string $username_prefix
     * @return array<string>|array<int>
     * @throws \Bristolian\PdoSimple\PdoSimpleException
     */
    public function searchUsernamesByPrefix(string $username_prefix): array
    {
        $sql = <<< SQL
select
  email_address
from 
  user_auth_email_password
where
  email_address like :like_string
limit
  :limit_number
SQL;

        $params = [
            ':like_string' => escapeMySqlLikeString($username_prefix) . '%',
            ':limit_number' => UserSearch::MAX_SEARCH_RESULTS
        ];

        return $this->pdoSimple->fetchAllRowsAsScalar($sql, $params);
    }
}
