<?php

// Auto-generated file do not edit

namespace Bristolian\Database;

class stored_meme
{
    const INSERT = <<< SQL
insert into stored_meme (
    id,
    normalized_name,
    original_filename,
    state,
    size,
    user_id
)
values (
    :id,
    :normalized_name,
    :original_filename,
    :state,
    :size,
    :user_id
)
SQL;

    const SELECT = <<< SQL
select
    id,
    normalized_name,
    original_filename,
    state,
    size,
    user_id
from
  stored_meme 
SQL;
}
