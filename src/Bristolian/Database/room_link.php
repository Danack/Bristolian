<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class room_link
{
    const INSERT = <<< SQL
insert into room_link (
    description,
    id,
    link_id,
    room_id,
    title
)
values (
    :description,
    :id,
    :link_id,
    :room_id,
    :title
)
SQL;

    const SELECT = <<< SQL
select
    description,
    id,
    link_id,
    room_id,
    title
from
  room_link 
SQL;
}
