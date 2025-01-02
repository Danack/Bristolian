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
    note,
    src_url,
    document_timestamp
)
values (
    :room_id,
    :stored_file_id,
    :description,
    :note,
    :src_url,
    :document_timestamp
)
SQL;

    const SELECT = <<< SQL
select
    room_id,
    stored_file_id,
    description,
    note,
    src_url,
    document_timestamp
from
  room_file 
SQL;
}
