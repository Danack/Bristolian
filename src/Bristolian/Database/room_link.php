<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

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
    title
from
  room_link 
SQL;
}
