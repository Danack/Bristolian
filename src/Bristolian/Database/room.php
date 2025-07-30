<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;
asda
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

    const UPDATE = <<< SQL
update
  room
set
  name = :name,
  purpose = :purpose
where
  id = :id
  limit 1
SQL;

}
