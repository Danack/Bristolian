<?php

// Auto-generated file do not edit

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
    title
from
  room_sourcelink
SQL;
}
