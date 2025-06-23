<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;

class processor
{
    const INSERT = <<< SQL
insert into processor (
    enabled,
    type
)
values (
    :enabled,
    :type
)
SQL;

    const SELECT = <<< SQL
select
    id,
    enabled,
    type,
    updated_at
from
  processor 
SQL;

    const UPDATE = <<< SQL
update
  processor
set
  enabled = :enabled,
  type = :type
where
  id = :id
  limit 1
SQL;
}
