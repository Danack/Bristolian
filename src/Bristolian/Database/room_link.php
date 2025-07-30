<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;
asdasd
class room_link
{
    const INSERT = <<< SQL
insert into room_link (
    id,
    link_id,
    room_id,
    description,
    title
)
values (
    :id,
    :link_id,
    :room_id,
    :description,
    :title
)
SQL;

    const SELECT = <<< SQL
select
    id,
    link_id,
    room_id,
    description,
    title,
    created_at
from
  room_link 
SQL;

    const UPDATE = <<< SQL
update
  room_link
set
  description = :description,
  title = :title
where
  id = :id
  limit 1
SQL;

}
