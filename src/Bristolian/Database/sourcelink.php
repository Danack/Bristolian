<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class sourcelink
{
    const INSERT = <<< SQL
insert into sourcelink (
    id,
    user_id,
    file_id,
    highlights_json,
    text
)
values (
    :id,
    :user_id,
    :file_id,
    :highlights_json,
    :text
)
SQL;

    const SELECT = <<< SQL
select
    id,
    user_id,
    file_id,
    highlights_json,
    text
from
  sourcelink 
SQL;
}
