<?php

namespace Bristolian\Repo\NicknameRepo;

use Bristolian\Model\User;
use Bristolian\Model\UserNickname;
use Bristolian\PdoSimple;

class PdoNicknameRepo implements NicknameRepo
{
    public function __construct(private PdoSimple $pdo_simple)
    {
    }

    public function getUserNickname(User $user): UserNickname|null
    {
        $sql = <<< SQL
select 
  user_id,
  nickname,
  version
from
  nicknames
where
  user_id = :user_id
order by
  version DESC
limit 1
SQL;

        return $this->pdo_simple->fetchOneAsObjectOrNull(
            $sql,
            [':user_id' => $user->getUserId()],
            UserNickname::class
        );
    }

    public function updateUserNickname(User $user, string $newNickname): UserNickname
    {

        $nested_select = <<< SQL
SELECT
  max(version)
FROM 
  nicknames as n
where 
  n.user_id = :user_id_2
group by
  n.user_id
SQL;

        $sql = <<< SQL
INSERT INTO nicknames (
  user_id,
  nickname,
  version
)
values (
  :user_id,
  :nickname,
  IFNULL(($nested_select), -1) + 1
)

SQL;

//    case when ($nested_select) is null then 0 else previous_version END


        $inserted = $this->pdo_simple->insert(
            $sql,
            [
                ':user_id' => $user->getUserId(),
                ':user_id_2' => $user->getUserId(),
                ':nickname' => $newNickname
            ],

        );


        return $this->getUserNickname($user);
    }
}