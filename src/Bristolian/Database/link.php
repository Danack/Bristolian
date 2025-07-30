<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;
adasd
class link
{
    const INSERT = <<< SQL
insert into link (
    id,
    user_id,
    url
)
values (
    :id,
    :user_id,
    :url
)
SQL;

    const SELECT = <<< SQL
select
    id,
    user_id,
    url,
    created_at
from
  link 
SQL;

    const UPDATE = <<< SQL
update
  link
set
  url = :url
where
  id = :id
  limit 1
SQL;

}
