<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class room_file
{
    const INSERT = <<< SQL
insert into room_file (
    description,
    document_timestamp,
    note,
    room_id,
    src_url,
    stored_file_id
)
values (
    :description,
    :document_timestamp,
    :note,
    :room_id,
    :src_url,
    :stored_file_id
)
SQL;

    const SELECT = <<< SQL
select
    description,
    document_timestamp,
    note,
    room_id,
    src_url,
    stored_file_id
from
  room_file 
SQL;
}
