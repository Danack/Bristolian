<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class sourcelink
{
    const INSERT = <<< SQL
insert into sourcelink (
    id,
    file_id,
    user_id,
    highlights_json,
    text
)
values (
    :id,
    :file_id,
    :user_id,
    :highlights_json,
    :text
)
SQL;

    const SELECT = <<< SQL
select
    id,
    file_id,
    user_id,
    highlights_json,
    text,
    created_at
from
  sourcelink 
SQL;
}
