<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;

class room_sourcelink
{
    const INSERT = <<< SQL
insert into room_sourcelink (
    id,
    room_id,
    sourcelink_id,
    title
)
values (
    :id,
    :room_id,
    :sourcelink_id,
    :title
)
SQL;

    const SELECT = <<< SQL
select
    id,
    room_id,
    sourcelink_id,
    title,
    created_at
from
  room_sourcelink 
SQL;

    const UPDATE = <<< SQL
update
  room_sourcelink
set
  title = :title
where
  id = :id
  limit 1
SQL;
}
