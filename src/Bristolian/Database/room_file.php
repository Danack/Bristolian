<?php

// Auto-generated file do not edit

// generated with 'php cli.php generate:php_table_helper_classes'

namespace Bristolian\Database;
asdad
class room_file
{
    const INSERT = <<< SQL
insert into room_file (
    room_id,
    stored_file_id,
    description,
    document_timestamp,
    note,
    src_url
)
values (
    :room_id,
    :stored_file_id,
    :description,
    :document_timestamp,
    :note,
    :src_url
)
SQL;

    const SELECT = <<< SQL
select
    room_id,
    stored_file_id,
    description,
    document_timestamp,
    note,
    src_url,
    created_at
from
  room_file 
SQL;

    const UPDATE = <<< SQL
update
  room_file
set
  description = :description,
  document_timestamp = :document_timestamp,
  note = :note,
  src_url = :src_url
where
  id = :id
  limit 1
SQL;

}
