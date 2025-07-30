<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;
asdad
class foi_requests
{
    const INSERT = <<< SQL
insert into foi_requests (
    foi_request_id,
    description,
    text,
    url
)
values (
    :foi_request_id,
    :description,
    :text,
    :url
)
SQL;

    const SELECT = <<< SQL
select
    foi_request_id,
    description,
    text,
    url,
    created_at
from
  foi_requests 
SQL;

    const UPDATE = <<< SQL
update
  foi_requests
set
  description = :description,
  text = :text,
  url = :url
where
  id = :id
  limit 1
SQL;

}
