<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class sourcelink
{
    const INSERT = <<< SQL
insert into sourcelink (
    file_id,
    highlights_json,
    id,
    text,
    user_id
)
values (
    :file_id,
    :highlights_json,
    :id,
    :text,
    :user_id
)
SQL;

    const SELECT = <<< SQL
select
    file_id,
    highlights_json,
    id,
    text,
    user_id
from
  sourcelink 
SQL;
}
