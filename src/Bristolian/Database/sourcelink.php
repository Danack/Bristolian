<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;

class sourcelink
{
    const INSERT = <<< SQL
insert into sourcelink (
    id,
    file_id,
    user_id,
    highlights_json,
    text
)
values (
    :id,
    :file_id,
    :user_id,
    :highlights_json,
    :text
)
SQL;

    const SELECT = <<< SQL
select
    id,
    file_id,
    user_id,
    highlights_json,
    text,
    created_at
from
  sourcelink 
SQL;

    const UPDATE = <<< SQL
update
  sourcelink
set
  highlights_json = :highlights_json,
  text = :text
where
  id = :id
  limit 1
SQL;
}
