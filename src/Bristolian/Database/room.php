<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class room
{
    const INSERT = <<< SQL
insert into room (
    id,
    owner_user_id,
    name,
    purpose
)
values (
    :id,
    :owner_user_id,
    :name,
    :purpose
)
SQL;

    const SELECT = <<< SQL
select
    id,
    owner_user_id,
    name,
    purpose,
    created_at
from
  room 
SQL;
}
