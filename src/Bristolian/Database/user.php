<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;

class user
{
    const INSERT = <<< SQL
insert into user (
    id
)
values (
    :id
)
SQL;

    const SELECT = <<< SQL
select
    id,
    created_at
from
  user 
SQL;

    const UPDATE = <<< SQL
update
  user
set

where
  id = :id
  limit 1
SQL;
}
