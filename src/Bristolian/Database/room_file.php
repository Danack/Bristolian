<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

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
    src_url
from
  room_file 
SQL;
}
