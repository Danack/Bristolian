<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class room_link
{
    const INSERT = <<< SQL
insert into room_link (
    id,
    room_id,
    link_id,
    title,
    description
)
values (
    :id,
    :room_id,
    :link_id,
    :title,
    :description
)
SQL;

    const SELECT = <<< SQL
select  
    id,
    room_id,
    link_id,
    title,
    description
from
  room_link
SQL;
}
