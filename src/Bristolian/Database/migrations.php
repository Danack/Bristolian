<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;

class migrations
{
    const INSERT = <<< SQL
insert into migrations (
    checksum,
    description
)
values (
    :checksum,
    :description
)
SQL;

    const SELECT = <<< SQL
select
    id,
    checksum,
    description,
    created_at
from
  migrations 
SQL;

    const UPDATE = <<< SQL
update
  migrations
set
  checksum = :checksum,
  description = :description
where
  id = :id
  limit 1
SQL;

}
