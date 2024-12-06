<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class room
{
    const INSERT = <<< SQL
insert into room (
    id,
    name,
    owner_user_id,
    purpose
)
values (
    :id,
    :name,
    :owner_user_id,
    :purpose
)
SQL;

    const SELECT = <<< SQL
select  
    id,
    name,
    owner_user_id,
    purpose
from
  room
SQL;
}
